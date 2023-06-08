<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ApiController extends Controller
{

  protected function authCheck($key)
  {

    $query = DB::table('master_auth_api')->where('api', 'simrs');
    $api_key = $query->first();
    if ($api_key) {
      if ($key == $api_key->key) {
        return 1;
      } else {
        return 0;
      }
    } else {
      return 0;
    }
  }

  public function get_enum_roomtype($kode_jenis)
  {

    if ($kode_jenis == 1) {
      return "rawat_inap";
    } else if ($kode_jenis == 2) {
      return "rawat_jalan";
    } else if ($kode_jenis == 3) {
      return "igd";
    } else if ($kode_jenis == 4) {
      return "rujukan";
    } else {
      return null;
    }
  }

  public function responseWithError($code, $message)
  {
    return response()->json([
      'error' => [
        'message' => $message,
        'status_code' => $code
      ]
    ]);
  }

  public function successResponse()
  {
    return response()->json([
      'success' => [
        'message' => 'Data transaksi berhasil sinkronisasi',
        'status_code' => 200
      ]
    ]);
  }

  public function successResponseUpdate()
  {
    return response()->json([
      'success' => [
        'message' => 'Data tes berhasil ditambahkan',
        'status_code' => 200
      ]
    ]);
  }

  public function insertPatient(Request $req)
  {
    $key_auth = $this->authCheck($req->header('x-api-key'));
    $demografi =  $req->input('demografi');
    $transaksi =  $req->input('transaksi');
    $tests =  $req->input('tes');

    // print_r($transaksi);
    // die;

    // store multiple arrays for json_encode purposes
    $store_json['demografi'] = $demografi;
    $store_json['transaksi'] = $transaksi;
    $store_json['tests'] = $tests;

    //hardcode handle for previous version ok jenis kelamin/gender
    if ($demografi['jk'] == "L") {
      $demografi['jk'] = "M";
    } elseif ($demografi['jk'] == "P") {
      $demografi['jk'] = "F";
    }

    //hardcode handle for previous version kode jenis ruangan/rooms
    $transaksi['kode_jenis'] = $this->get_enum_roomtype($transaksi['kode_jenis']);
    if ($key_auth == 1) {
      DB::beginTransaction();

      // Insert Patient

      $query = DB::table('patients')->where('medrec', $demografi['no_rkm_medis']);
      $patient_data = $query->first();
      if (empty($patient_data)) {
        DB::table('patients')
          ->insert([
            'nik' => $demografi['nik'],
            'name' => $demografi['nama_pasien'],
            'email' => isset($demografi['email']) ? $demografi['email'] : "",
            'phone' => $demografi['no_telp'],
            'medrec' => $demografi['no_rkm_medis'],
            'gender' => $demografi['jk'],
            'birthdate' => $demografi['tgl_lahir'],
            'address' => $demografi['alamat']
          ]);
        $patient_id = DB::getPdo()->lastInsertId();
      } else {
        $patient_id = $patient_data->id;
      }

      // Insert Transaction

      // Insert Rooms
      $query = DB::table('rooms')->where('general_code', $transaksi['kode_ruangan']);
      $room_data = $query->first();
      if (empty($room_data)) {
        DB::rollback();

        DB::table('log_integrations')
          ->insert([
            'no_order' => $transaksi['no_order'],
            'return_result' => json_encode($store_json),
            'timestamp' => Carbon::now(),
            'type' => "POST DATA",
            'status' => "insert_transaction_failed",
            'status_sequence' => 0,
            'notes' => "Ruangan (" . $transaksi['ruangan'] . ") dengan kode " . $transaksi['kode_ruangan'] . " tidak terdaftar pada data master LICA",
            'status_2' => 0,
            'created_at' => Carbon::now()
          ]);

        return $this->responseWithError(400, "Ruangan (" . $transaksi['ruangan'] . ") dengan kode " . $transaksi['kode_ruangan'] . " tidak terdaftar pada data master LICA");
      }

      // Insert Doctors
      $query = DB::table('doctors')->where('general_code', $transaksi['kode_dokter']);
      $doctor_data = $query->first();
      if (empty($doctor_data)) {
        DB::rollback();

        DB::table('log_integrations')
          ->insert([
            'no_order' => $transaksi['no_order'],
            'return_result' => json_encode($store_json),
            'timestamp' => Carbon::now(),
            'type' => "POST DATA",
            'status' => "insert_transaction_failed",
            'status_sequence' => 0,
            'notes' => "Dokter (" . $transaksi['dokter'] . ") dengan kode " . $transaksi['kode_dokter'] . " tidak terdaftar pada data master LICA",
            'status_2' => 0,
            'created_at' => Carbon::now()
          ]);

        return $this->responseWithError(400, "Dokter (" . $transaksi['dokter'] . ") dengan kode " . $transaksi['kode_dokter'] . " tidak terdaftar pada data master LICA");
      }

      // Insert Insurances
      $query = DB::table('insurances')->where('general_code', $transaksi['kode_pembayaran']);
      $insurance_data = $query->first();
      if (empty($insurance_data)) {
        DB::rollback();

        DB::table('log_integrations')
          ->insert([
            'no_order' => $transaksi['no_order'],
            'return_result' => json_encode($store_json),
            'timestamp' => Carbon::now(),
            'type' => "POST DATA",
            'status' => "insert_transaction_failed",
            'status_sequence' => 0,
            'notes' => "Jenis Pembayaran/Asuransi (" . $transaksi['pembayaran'] . ") dengan kode " . $transaksi['kode_pembayaran'] . " tidak terdaftar pada data master LICA",
            'status_2' => 0,
            'created_at' => Carbon::now()
          ]);

        return $this->responseWithError(400, "Jenis Pembayaran/Asuransi (" . $transaksi['pembayaran'] . ") dengan kode " . $transaksi['kode_pembayaran'] . " tidak terdaftar pada data master LICA");
      }

      // Check Transaction exist or not
      $exist_query = DB::table('transactions')->where('no_order', $transaksi['no_order']);
      $check_transaction_exist = $exist_query->first();

      if (!$check_transaction_exist) {

        $type = $this->getTransactionType($transaksi['kode_jenis']);
        $transactionInsertData = [
          'patient_id' => $patient_id,
          'room_id' => $room_data->id,
          'doctor_id' => $doctor_data->id,
          'insurance_id' => $insurance_data->id,
          'type' => $transaksi['kode_jenis'],
          'transaction_id_label' => $transaksi['no_order'],
          'no_order' => $transaksi['no_order'],
          'status' => 0,
          'created_time' => Carbon::now()->toDateTimeString(),
          'created_at' => Carbon::now()->toDateTimeString()
        ];

        if ($room_data->auto_checkin || $room_data->auto_draw) {
          $prefixDate = date('ymd');
          $countExistingData = \App\Transaction::where('no_lab', 'like', $prefixDate . '%')->count();
          $trxId = str_pad($countExistingData, 3, '0', STR_PAD_LEFT);
          $check =  \App\Transaction::where('no_lab', $prefixDate . $trxId)->exists();

          while ($check) {
            $countExistingData += 1;
            $trxId = str_pad($countExistingData, 3, '0', STR_PAD_LEFT);
            $check =  \App\Transaction::where('no_lab', $prefixDate . $trxId)->exists();
          }
          $transactionInsertData['no_lab'] = $prefixDate . $trxId;
          $transactionInsertData['checkin_time'] = Carbon::now();
        }
        DB::table('transactions')
          ->insert($transactionInsertData);

        // get transaction id
        $transaction_id = DB::getPdo()->lastInsertId();

        // Transaction Test
        foreach ($tests as $test => $value) {
          $query = DB::table('packages')->where('general_code', $value['kode_jenis_tes']);
          $package_data = $query->first();

          // Package Test
          if (!empty($package_data)) {
            $query = DB::table('package_tests')->where('package_id', $package_data->id);
            $package_tests = $query->get();

            foreach ($package_tests as $package_test) {

              //check have default or not
              $checkDefaultAnalyzer = \App\Analyzer::where('group_id', $package_data->group_id)->where('is_default', 1)->first();
              if ($checkDefaultAnalyzer) {
                DB::table('transaction_tests')
                  ->insert([
                    'transaction_id' => $transaction_id,
                    'test_id' => $package_test->test_id,
                    'check_code' => $value['kode_jenis_tes'],
                    'package_id' => $package_data->id,
                    'group_id' => $package_data->group_id,
                    'analyzer_id' => $checkDefaultAnalyzer->id,
                    'created_at' => Carbon::now()
                  ]);
              } else {
                DB::table('transaction_tests')
                  ->insert([
                    'transaction_id' => $transaction_id,
                    'test_id' => $package_test->test_id,
                    'check_code' => $value['kode_jenis_tes'],
                    'package_id' => $package_data->id,
                    'group_id' => $package_data->group_id,
                    'analyzer_id' => NULL,
                    'created_at' => Carbon::now()
                  ]);
              }
            }
          } else {
            // Single Test

            $query = DB::table('tests')->where('tests.general_code', $value['kode_jenis_tes']);
            $test_data = $query->first();
            if (empty($test_data)) {
              DB::rollback();

              DB::table('log_integrations')
                ->insert([
                  'no_order' => $transaksi['no_order'],
                  'return_result' => json_encode($store_json),
                  'timestamp' => Carbon::now(),
                  'type' => "POST DATA",
                  'status' => "insert_transaction_failed",
                  'status_sequence' => 0,
                  'notes' => "Data tes (" . $value['nama_tes'] . ") dengan kode " . $transaksi['kode_jenis_tes'] . " tidak terdaftar pada master data LICA",
                  'status_2' => 0
                ]);

              // delete transactions record
              DB::table('transactions')->where('id', $transaction_id)->delete();
              // delete transaction_tests record
              DB::table('transaction_tests')->where('transaction_id', $transaction_id)->delete();

              return $this->responseWithError(400, "Data tes (" . $value['nama_tes'] . ") dengan kode " . $transaksi['kode_jenis_tes'] . " tidak terdaftar pada master data LICA");
            } else {

              //check have default or not
              $checkDefaultAnalyzer = \App\Analyzer::where('group_id', $test_data->group_id)->where('is_default', 1)->first();
              if ($checkDefaultAnalyzer) {
                DB::table('transaction_tests')
                  ->insert([
                    'transaction_id' => $transaction_id,
                    'test_id' => $test_data->id,
                    'check_code' => $value['kode_jenis_tes'],
                    'analyzer_id' => $checkDefaultAnalyzer->id,
                    'group_id' => $test_data->group_id,
                    'created_at' => Carbon::now()
                  ]);
              } else {
                DB::table('transaction_tests')
                  ->insert([
                    'transaction_id' => $transaction_id,
                    'test_id' => $test_data->id,
                    'check_code' => $value['kode_jenis_tes'],
                    'analyzer_id' => NULL,
                    'group_id' => $test_data->group_id,
                    'created_at' => Carbon::now()
                  ]);
              }
            }
          }
        }

        DB::table('log_integrations')
          ->insert([
            'no_order' => $transaksi['no_order'],
            'return_result' => json_encode($store_json),
            'type' => "POST DATA",
            'status' => "insert_transaction_success",
            'status_sequence' => 1,
            'notes' => "Data transaksi berhasil sinkronisasi",
            'status_2' => 1,
            'timestamp' => Carbon::now(),
            'created_at' => Carbon::now(),
          ]);

        // DB::table('log_integrations')->where('no_order', $transaksi['no_order'])->where('status_2', '=', 0)->delete();

        DB::commit();
        return $this->successResponse();
      } else {

        // FLOW MAP TRANSACTION HAS BEEN REGISTERED IN LICA

        // if the order_number has been registered in LICA
        // then check the data from HIS/SIMRS (update or delete data) method

        // (ADD new test)
        // first, check order_number in LICA, WHETHER the transaction status was 0 
        // IF transaction status was 0 AND DRAW STATUS was 0
        // THEN transaction data can be added with new test

        // (UPDATE exist test or DELETE)
        // second, check order_number in LICA, WHETHER the transaction status was 0 
        // IF transaction status was 0 AND DRAW STATUS was 0
        // THEN transaction tests data can be UPDATED or DELETED

        // ELSE IF transaction status was 2 (post position), THEN transaction can not be added new test OR transaction tests data can not be UPDATED or DELETED

        $transaction_data = $check_transaction_exist;
        $transaction_id = $transaction_data->id;

        $transaction_test_data = DB::table('transaction_tests')->where('transaction_id', $transaction_data->id)->first();
        $draw_by = $transaction_test_data->draw_by;
        $draw_time = $transaction_test_data->draw_time;

        // print_r($transaction_test_data);
        // die;

        // $transaction_test = DB::table('transaction_tests')->where('id', $transaction_data->id)->first();

        if ($transaction_data->status == 0 || $transaction_data->status == 1) {

          foreach ($tests as $test => $value) {
            $query = DB::table('packages')->where('general_code', $value['kode_jenis_tes']);
            $package_data = $query->first();

            // Package Test
            if (!empty($package_data)) {

              $transaction_test = DB::table('transaction_tests')
                ->where('transaction_id', $transaction_data->id)
                ->where('check_code', $value['kode_jenis_tes'])
                ->first();

              if (!$transaction_test) {
                $query = DB::table('package_tests')->where('package_id', $package_data->id);
                $package_tests = $query->get();

                foreach ($package_tests as $package_test) {
                  //check have default or not
                  $checkDefaultAnalyzer = \App\Analyzer::where('group_id', $package_data->group_id)->where('is_default', 1)->first();
                  if ($checkDefaultAnalyzer) {
                    DB::table('transaction_tests')
                      ->insert([
                        'transaction_id' => $transaction_id,
                        'test_id' => $package_test->test_id,
                        'check_code' => $value['kode_jenis_tes'],
                        'package_id' => $package_data->id,
                        'group_id' => $package_data->group_id,
                        'analyzer_id' => $checkDefaultAnalyzer->id,
                        'mark_duplo' => 0,
                        'type' => "package",
                        'result_number' => NULL,
                        'result_label' => NULL,
                        'result_text' => NULL,
                        'draw' => 1,
                        'draw_by' => $draw_by,
                        'draw_memo' => NULL,
                        'undraw_memo' => NULL,
                        'draw_time' => $draw_time,
                        'input_time' => NULL,
                        'result_status' => NULL,
                        'verify' => 0,
                        'validate' => 0,
                        'report_status' => 0,
                        'report_by' => NULL,
                        'report_to' => NULL,
                        'memo_test' => NULL,
                        'verify_by' => NULL,
                        'validate_by' => NULL,
                        'verify_time' => NULL,
                        'validate_time' => NULL,
                        'report_time' => NULL,
                        'check_code' => $value['kode_jenis_tes'],
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                      ]);
                  } else {

                    $analyzerFromInterfacing = DB::table('interfacings')->where('test_id', $package_test->test_id)->first();
                    if ($analyzerFromInterfacing) {
                      $analyzer_id = $analyzerFromInterfacing->analyzer_id;
                    } else {
                      $analyzer_id = NULL;
                    }

                    DB::table('transaction_tests')
                      ->insert([
                        'transaction_id' => $transaction_id,
                        'test_id' => $package_test->test_id,
                        'check_code' => $value['kode_jenis_tes'],
                        'package_id' => $package_data->id,
                        'group_id' => $package_data->group_id,
                        'analyzer_id' => NULL,
                        'mark_duplo' => 0,
                        'type' => "package",
                        'result_number' => NULL,
                        'result_label' => NULL,
                        'result_text' => NULL,
                        'draw' => 1,
                        'draw_by' => $draw_by,
                        'draw_memo' => NULL,
                        'undraw_memo' => NULL,
                        'draw_time' => $draw_time,
                        'input_time' => NULL,
                        'result_status' => NULL,
                        'verify' => 0,
                        'validate' => 0,
                        'report_status' => 0,
                        'report_by' => NULL,
                        'report_to' => NULL,
                        'memo_test' => NULL,
                        'verify_by' => NULL,
                        'validate_by' => NULL,
                        'verify_time' => NULL,
                        'validate_time' => NULL,
                        'report_time' => NULL,
                        'check_code' => $value['kode_jenis_tes'],
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                      ]);
                  }
                }
              }
            } else {
              // Single Test

              $transaction_test = DB::table('transaction_tests')
                ->where('transaction_id', $transaction_data->id)
                ->where('check_code', $value['kode_jenis_tes'])
                ->first();

              if (!$transaction_test) {

                // echo $value['kode_jenis_tes'] . "<br>";
                // echo "belum ada" . "<br>";

                $queryTest = DB::table('tests')->where('tests.general_code', $value['kode_jenis_tes']);
                $test_data = $queryTest->first();

                if ($test_data) {
                  //check have default or not
                  $checkDefaultAnalyzer = \App\Analyzer::where('group_id', $test_data->group_id)->where('is_default', 1)->first();
                  if ($checkDefaultAnalyzer) {
                    DB::table('transaction_tests')
                      ->insert([
                        'transaction_id' => $transaction_id,
                        'test_id' => $test_data->id,
                        'package_id' => NULL,
                        'group_id' => $test_data->group_id,
                        'analyzer_id' => $checkDefaultAnalyzer->id,
                        'mark_duplo' => 0,
                        'type' => "single",
                        'result_number' => NULL,
                        'result_label' => NULL,
                        'result_text' => NULL,
                        'draw' => 1,
                        'draw_by' => $draw_by,
                        'draw_memo' => NULL,
                        'undraw_memo' => NULL,
                        'draw_time' => $draw_time,
                        'input_time' => NULL,
                        'result_status' => NULL,
                        'verify' => 0,
                        'validate' => 0,
                        'report_status' => 0,
                        'report_by' => NULL,
                        'report_to' => NULL,
                        'memo_test' => NULL,
                        'verify_by' => NULL,
                        'validate_by' => NULL,
                        'verify_time' => NULL,
                        'validate_time' => NULL,
                        'report_time' => NULL,
                        'check_code' => $value['kode_jenis_tes'],
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                      ]);
                  } else {

                    $analyzerFromInterfacing = DB::table('interfacings')->where('test_id', $$test_data->id)->first();
                    $analyzer_id = $analyzerFromInterfacing->analyzer_id;

                    DB::table('transaction_tests')
                      ->insert([
                        'transaction_id' => $transaction_id,
                        'test_id' => $test_data->id,
                        'package_id' => NULL,
                        'group_id' => $test_data->group_id,
                        'analyzer_id' => $analyzer_id,
                        'mark_duplo' => 0,
                        'type' => "single",
                        'result_number' => NULL,
                        'result_label' => NULL,
                        'result_text' => NULL,
                        'draw' => 1,
                        'draw_by' => $draw_by,
                        'draw_memo' => NULL,
                        'undraw_memo' => NULL,
                        'draw_time' => $draw_time,
                        'input_time' => NULL,
                        'result_status' => NULL,
                        'verify' => 0,
                        'validate' => 0,
                        'report_status' => 0,
                        'report_by' => NULL,
                        'report_to' => NULL,
                        'memo_test' => NULL,
                        'verify_by' => NULL,
                        'validate_by' => NULL,
                        'verify_time' => NULL,
                        'validate_time' => NULL,
                        'report_time' => NULL,
                        'check_code' => $value['kode_jenis_tes'],
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                      ]);
                  }
                }
              }
              // else {
              //   echo $value['kode_jenis_tes'] . "<br>";
              //   echo "sudah ada" . "<br>";
              //   return $this->responseWithError(401, "Tidak ada data yang ditambahkan");
              // }
            }
          }
          DB::commit();
          return $this->successResponseUpdate();
        } else {
          return $this->responseWithError(401, "Status transaksi sudah di Post Analytic!");
        }
      }
    } else {
      DB::table('log_integrations')
        ->insert([
          'created_at' => Carbon::now(),
          'no_order' => $transaksi['no_order'],
          'return_result' => 'Autentikasi tidak valid',
          'timestamp' => Carbon::now(),
          'status' => "insert_transaction_failed",
        ]);
      return $this->responseWithError(401, "Autentikasi tidak valid");
    }
  }

  public function getResult(Request $req, $order_id)
  {
    $key_auth = $this->authCheck($req->header('x-api-key'));

    if ($key_auth == 1) {
      $query = DB::table('finish_transactions')->where('finish_transactions.no_order', $order_id);
      $transaction_data = $query->first();
      if (!$transaction_data) {
        return $this->responseWithError(400, "Data Tidak Ditemukan");
      }
      $born = Carbon::createFromFormat('Y-m-d', $transaction_data->patient_birthdate);
      $birthdate = $born->diff(Carbon::now())->format('%y Thn / %m Bln / %d Hr');
      $birthday = $born->diff(Carbon::now())->days;
      // $query = DB::table('finish_transaction_tests')->selectRaw('finish_transaction_tests.result_status as flag, finish_transaction_tests.test_unit as unit, finish_transaction_tests.result as result, finish_transaction_tests.test_name as test_name, finish_transaction_tests.normal_value as nilai_normal, finish_transaction_tests.memo_test as notes')->join('transactions', 'finish_transaction_tests.transaction_id', '=', 'transactions.id')->where('transactions.no_order', $order_id);
      // $result_data = $query->get();

      $tests = DB::table('finish_transaction_tests')
        ->select('finish_transaction_tests.*', 'packages.general_code as general_code_package', 'tests.general_code as general_code_single')
        ->leftJoin('packages', 'finish_transaction_tests.package_id', '=', 'packages.id')
        ->leftJoin('tests', 'finish_transaction_tests.test_id', '=', 'tests.id')
        ->where('finish_transaction_id', $transaction_data->id)
        ->get();
      $test_result = [];
      foreach ($tests as $val => $test) {

        // general code package
        if ($test->package_id != null) {
          $general_code_package = $test->general_code_package;
        } else {
          $general_code_package = 0;
        }

        // general code single
        if ($test->general_code_single != null) {
          $general_code_single = $test->general_code_single;
        } else {
          $general_code_single = 0;
        }

        $normal_value = strip_tags($test->normal_value);
        
        $test_result[$val]['flag_duplo'] = $test->mark_duplo;
        $test_result[$val]['flag'] = $test->result_status_label;
        $test_result[$val]['unit'] = $test->unit;
        $test_result[$val]['result'] = $test->global_result;
        $test_result[$val]['test_id'] = $general_code_single;
        $test_result[$val]['test_name'] = $test->test_name;
        $test_result[$val]['test_id_paket'] = $general_code_package;
        $test_result[$val]['group_name'] = $test->group_name;
        $test_result[$val]['nilai_normal'] = $normal_value;
        $test_result[$val]['notes'] = $test->memo_test;
        $test_result[$val]['sequence'] = $test->sequence;

      }

      $data['no_ref'] = $transaction_data->no_order;
      $data['tgl_kirim'] = $transaction_data->post_time;
      $data['hasil'] = $test_result;
      return response()->json($data);
    } else {
      DB::table('log_integrations')
        ->insert([
          'no_order' => $order_id,
          'return_result' => 'Autentikasi tidak valid',
          'timestamp' => Carbon::now(),
          'created_at' => Carbon::now(),
          'status' => "GET DATA",
        ]);
      return $this->responseWithError(401, "Autentikasi tidak valid");
    }
  }

  public function sendResult($no_order)
  {
    $url = url('api/get_result/'.$no_order);
		$query = DB::table('master_auth_api')->where('api', 'simrs');
		$api_key = $query->first();
		$key = $api_key->key;

		// print_r($url);
    $ch = curl_init($url);
  
    $header = array(
                'Content-Type: application/json',
                'x-api-key: ' . $key,
                'key-ws: kKbau319'
    );
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_POST, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$result = curl_exec($ch);
		
		$endpoints = DB::table('master_result_integration')->get();
		foreach($endpoints as $endpoint){
			$curl = curl_init($endpoint->url);
			$header = [];
			$parameters = DB::table('master_parameter_integration')->where('id_integration', $endpoint->id)->get();
			foreach($parameters as $param){
				$header[] = $param->parameter_name . ': ' . $param->parameter_value;
			}
			
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

			curl_setopt($curl, CURLOPT_POSTFIELDS, $result);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			
			$simrs_response = curl_exec($curl);

      $decode_response = json_decode($simrs_response);
      foreach($decode_response as $responseData){
        if($responseData->message == "OK" && $responseData->code == 200){
            $result_data = json_decode($result);
            DB::table('log_integrations')
              ->insert([
                'created_at' => Carbon::now(),
                'no_order' => $result_data->no_ref,
                'return_result' => $result,
                'simrs_response' => $simrs_response,
                'timestamp' => Carbon::now(),
                'type' => 'SEND DATA',
                'status' => "send_result_success",
                'status_sequence' => 0,
                'notes' => 'http://10.0.06/SIMRS/web_service/Lis/testResult',
                'status_2' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => null
              ]);
        }else{
          $result_data = json_decode($result);
            DB::table('log_integrations')
              ->insert([
                'created_at' => Carbon::now(),
                'no_order' => $result_data->no_ref,
                'return_result' => $result,
                'simrs_response' => $simrs_response,
                'timestamp' => Carbon::now(),
                'type' => 'SEND DATA',
                'status' => "send_result_failed",
                'status_sequence' => 0,
                'notes' => 'http://10.0.06/SIMRS/web_service/Lis/testResult',
                'status_2' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => null
              ]);
        }
      }
			
		}
  }

  public function sendHalfData($order_id)
  {
    $query = DB::table('transactions')
      ->select('transactions.*', 'patients.name as patient_name', 'patients.gender', 'patients.birthdate')
      ->leftJoin('patients', 'transactions.patient_id', '=', 'patients.id')
      ->where('no_order', $order_id);
    $transaction_data = $query->first();

    $patientGender = $transaction_data->gender;
    $bornDate = $transaction_data->birthdate;
    $ageInDays = Carbon::createFromFormat('Y-m-d', $bornDate)->diffInDays(Carbon::now());

    if (!$transaction_data) {
      return $this->responseWithError(400, "Data Tidak Ditemukan");
    }

    $tests = DB::table('transaction_tests')
      ->select('transaction_tests.*', 'tests.name as test_name', 'tests.unit', 'tests.sequence', 'groups.name as group_name')
      ->leftJoin('tests', 'transaction_tests.test_id', '=', 'tests.id')
      ->leftJoin('groups', 'transaction_tests.group_id', '=', 'groups.id')
      ->where('transaction_id', $transaction_data->id)
      ->where('validate', 1)->get();
    $test_result = [];

    // print_r($tests);
    // die;

    foreach ($tests as $val => $test) {

      // result flagging
      if ($test->result_number || $test->result_label) {
        switch ($test->result_status) {
          case '1':
            $flag = "Normal";
            break;
          case '2':
            $flag = "Low";
            break;
          case '3':
            $flag = "High";
            break;
          case '4':
            $flag = "Abnormal";
            break;
          case '5':
            $flag = "Critical";
            break;
        }
      } else {
        $flag = null;
      }

      // result value
      if ($test->result_number) {
        $result = $test->result_number;
        $range = \App\Range::where('test_id', $test->test_id)->where('min_age', '<=', $ageInDays)->where('max_age', '>=', $ageInDays)->first();

        if ($patientGender == 'M') {
          $normal_value = str_replace(['<p>', '</p>'], '', $range->normal_male);
        } else {
          $normal_value = str_replace(['<p>', '</p>'], '', $range->normal_female);
        }
      } else if ($test->result_label) {
        $resultLabel = \App\Result::where('id', $test->result_label)->first();
        $result = $resultLabel->result;
        $normal_value = $resultLabel->status;
      } else {
        $result = $test->result_text;
      }

      $test_result[$val]['flag_duplo'] = $test->mark_duplo;
      $test_result[$val]['flag'] = $flag;
      $test_result[$val]['unit'] = $test->unit;
      $test_result[$val]['result'] = $result;
      $test_result[$val]['test_id'] = $test->test_id;
      $test_result[$val]['test_name'] = $test->test_name;
      $test_result[$val]['test_id_paket'] = $test->package_id;
      $test_result[$val]['group_name'] = $test->group_name;
      $test_result[$val]['nilai_normal'] = $normal_value;
      $test_result[$val]['notes'] = $test->memo_test;
      $test_result[$val]['sequence'] = $test->sequence;
      
    }

    $arrayData = array(
      'no_ref' => $transaction_data->no_order,
      'tgl_kirim' => date('Y-m-d H:i:s'),
      'hasil' => $test_result
    );

    // payload Data
    $result = json_encode($arrayData);

    // send to endpoint needs
    $header = array(
      'Content-Type: application/json',
      'x-api-key: ' . 'licaapi',
      'key-ws: kKbau319'
    );

    $endpoints = DB::table('master_result_integration')->get();
		foreach($endpoints as $endpoint){
			$curl = curl_init($endpoint->url);
			$header = [];
			$parameters = DB::table('master_parameter_integration')->where('id_integration', $endpoint->id)->get();
			foreach($parameters as $param){
				$header[] = $param->parameter_name . ': ' . $param->parameter_value;
			}
			
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

			curl_setopt($curl, CURLOPT_POSTFIELDS, $result);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			
			$simrs_response = curl_exec($curl);

      $decode_response = json_decode($simrs_response);
      foreach($decode_response as $responseData){
        if($responseData->message == "OK" && $responseData->code == 200){
            $result_data = json_decode($result);
            DB::table('log_integrations')
              ->insert([
                'created_at' => Carbon::now(),
                'no_order' => $result_data->no_ref,
                'return_result' => $result,
                'simrs_response' => $simrs_response,
                'timestamp' => Carbon::now(),
                'type' => 'SEND DATA',
                'status' => "send_half_data_success",
                'status_sequence' => 0,
                'notes' => 'http://10.0.06/SIMRS/web_service/Lis/testResult',
                'status_2' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => null
              ]);
        }else{
          $result_data = json_decode($result);
            DB::table('log_integrations')
              ->insert([
                'created_at' => Carbon::now(),
                'no_order' => $result_data->no_ref,
                'return_result' => $result,
                'simrs_response' => $simrs_response,
                'timestamp' => Carbon::now(),
                'type' => 'SEND DATA',
                'status' => "send_half_data_failed",
                'status_sequence' => 0,
                'notes' => 'http://10.0.06/SIMRS/web_service/Lis/testResult',
                'status_2' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => null
              ]);
        }
      }
			
		}

  }

  // public function sendHalfDataBackup($order_id)
  // {
  //   $query = DB::table('transactions')
  //     ->select('transactions.*', 'patients.name as patient_name', 'patients.gender', 'patients.birthdate')
  //     ->leftJoin('patients', 'transactions.patient_id', '=', 'patients.id')
  //     ->where('no_order', $order_id);
  //   $transaction_data = $query->first();

  //   $patientGender = $transaction_data->gender;
  //   $bornDate = $transaction_data->birthdate;
  //   $ageInDays = Carbon::createFromFormat('Y-m-d', $bornDate)->diffInDays(Carbon::now());

  //   if (!$transaction_data) {
  //     return $this->responseWithError(400, "Data Tidak Ditemukan");
  //   }

  //   $tests = DB::table('transaction_tests')
  //     ->select('transaction_tests.*', 'tests.name as test_name', 'tests.unit', 'tests.sequence', 'groups.name as group_name')
  //     ->leftJoin('tests', 'transaction_tests.test_id', '=', 'tests.id')
  //     ->leftJoin('groups', 'transaction_tests.group_id', '=', 'groups.id')
  //     ->where('transaction_id', $transaction_data->id)
  //     ->where('validate', 1)->get();
  //   $test_result = [];

  //   // print_r($tests);
  //   // die;

  //   foreach ($tests as $val => $test) {

  //     // result flagging
  //     if ($test->result_number || $test->result_label) {
  //       switch ($test->result_status) {
  //         case '1':
  //           $flag = "Normal";
  //           break;
  //         case '2':
  //           $flag = "Low";
  //           break;
  //         case '3':
  //           $flag = "High";
  //           break;
  //         case '4':
  //           $flag = "Abnormal";
  //           break;
  //         case '5':
  //           $flag = "Critical";
  //           break;
  //       }
  //     } else {
  //       $flag = null;
  //     }

  //     // result value
  //     if ($test->result_number) {
  //       $result = $test->result_number;
  //       $range = \App\Range::where('test_id', $test->test_id)->where('min_age', '<=', $ageInDays)->where('max_age', '>=', $ageInDays)->first();

  //       if ($patientGender == 'M') {
  //         $normal_value = str_replace(['<p>', '</p>'], '', $range->normal_male);
  //       } else {
  //         $normal_value = str_replace(['<p>', '</p>'], '', $range->normal_female);
  //       }
  //     } else if ($test->result_label) {
  //       $resultLabel = \App\Result::where('id', $test->result_label)->first();
  //       $result = $resultLabel->result;
  //       $normal_value = $resultLabel->status;
  //     } else {
  //       $result = $test->result_text;
  //     }

  //     $test_result[$val]['flag_duplo'] = $test->mark_duplo;
  //     $test_result[$val]['flag'] = $flag;
  //     $test_result[$val]['unit'] = $test->unit;
  //     $test_result[$val]['result'] = $result;
  //     $test_result[$val]['test_id'] = $test->test_id;
  //     $test_result[$val]['test_name'] = $test->test_name;
  //     $test_result[$val]['group_name'] = $test->group_name;
  //     $test_result[$val]['nilai_normal'] = $normal_value;
  //     $test_result[$val]['notes'] = $test->memo_test;
  //     $test_result[$val]['sequence'] = $test->sequence;
  //   }

  //   $arrayData = array(
  //     'no_ref' => $transaction_data->no_order,
  //     'tgl_kirim' => date('Y-m-d H:i:s'),
  //     'hasil' => $test_result
  //   );

  //   // payload Data
  //   $payload = json_encode($arrayData);

  //   // print_r($payload);
  //   // die;

  //   // simrs URL
  //   $url = 'http://10.0.06/SIMRS/web_service/Lis/testResult';
  //   $ch = curl_init($url);

  //   $header = [];
  //   $header = array(
  //     'Content-Type: application/json',
  //     'x-api-key: ' . 'licaapi',
  //     'key-ws: ' . 'kKbau319'
  //   );

  //   curl_setopt($ch, CURLOPT_POST, 1);
  //   curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

  //   curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
  //   curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  //   //execute the POST request
  //   $result = curl_exec($ch);
  //   $result_data = json_decode($result);

  //   DB::table('log_integrations')
  //     ->insert([
  //       'created_at' => Carbon::now(),
  //       'no_order' => $transaction_data->no_order,
  //       'return_result' => $payload,
  //       'timestamp' => Carbon::now(),
  //       'type' => 'SEND DATA',
  //       'status' => "send_result_success",
  //       'status_sequence' => 0,
  //       'notes' => 'http://10.0.06/SIMRS/web_service/Lis/testResult',
  //       'status_2' => 0,
  //       'created_at' => Carbon::now(),
  //       'updated_at' => null
  //     ]);
  // }

  // public function sendResultBackup($no_order)
  // {
  //   $query = DB::table('finish_transactions')->where('finish_transactions.no_order', $no_order);
  //   $transaction_data = $query->first();
  //   if (!$transaction_data) {
  //     return $this->responseWithError(400, "Data Tidak Ditemukan");
  //   }

  //   $born = Carbon::createFromFormat('Y-m-d', $transaction_data->patient_birthdate);
  //   $birthdate = $born->diff(Carbon::now())->format('%y Thn / %m Bln / %d Hr');
  //   $birthday = $born->diff(Carbon::now())->days;

  //   $tests = DB::table('finish_transaction_tests')
  //     ->select('finish_transaction_tests.*', 'packages.general_code as general_code_package', 'tests.general_code as general_code_single')
  //     ->leftJoin('packages', 'finish_transaction_tests.package_id', '=', 'packages.id')
  //     ->leftJoin('tests', 'finish_transaction_tests.test_id', '=', 'tests.id')
  //     ->where('finish_transaction_id', $transaction_data->id)
  //     ->get();

  //   $test_result = [];
  //   foreach ($tests as $val => $test) {
  //     // general code package
  //     if ($test->package_id != null) {
  //       $general_code_package = $test->general_code_package;
  //     } else {
  //       $general_code_package = $test->general_code_single;
  //     }

  //     // general code single
  //     if ($test->general_code_single != null) {
  //       $general_code_single = $test->general_code_single;
  //     } else {
  //       $general_code_single = 0;
  //     }

  //     $normal_value = strip_tags($test->normal_value);

  //     $test_result[$val]['flag_duplo'] = $test->mark_duplo;
  //     $test_result[$val]['flag'] = $test->result_status_label;
  //     $test_result[$val]['unit'] = $test->unit;
  //     $test_result[$val]['result'] = $test->global_result;
  //     $test_result[$val]['test_id'] = $general_code_single;
  //     $test_result[$val]['test_name'] = $test->test_name;
  //     $test_result[$val]['test_id_paket'] = $general_code_package;
  //     $test_result[$val]['group_test'] = $test->group_name;
  //     $test_result[$val]['nilai_normal'] = $normal_value;
  //     $test_result[$val]['notes'] = $test->memo_test;
  //     $test_result[$val]['sequence'] = $test->sequence;
  //   }

  //   $arrayData = array(
  //     'no_ref' => $transaction_data->no_order,
  //     'tgl_kirim' => $transaction_data->post_time,
  //     'hasil' => $test_result
  //   );

  //   // payload Data
  //   $payload = json_encode($arrayData);

  //   // echo $payload;
  //   // die;

  //   // simrs URL
  //   $url = 'http://10.0.06/SIMRS/web_service/Lis/testResult';
  //   $ch = curl_init($url);

  //   // echo $ch;
  //   // die;

  //   $header = [];
  //   $header = array(
  //     'Content-Type: application/json',
  //     'x-api-key: ' . 'licaapi', 
  //     'key-ws: ' . 'kKbau319'
  //   );

  //   curl_setopt($ch, CURLOPT_POST, 1);
  //   curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

  //   curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
  //   curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  //   //execute the POST request
  //   $result = curl_exec($ch);
  //   $result_data = json_decode($result);

  //   // print_r($result_data);
  //   // die;

  //   DB::table('log_integrations')
  //     ->insert([
  //       'created_at' => Carbon::now(),
  //       'no_order' => $transaction_data->no_order,
  //       'return_result' => $payload,
  //       'timestamp' => Carbon::now(),
  //       'type' => 'SEND DATA',
  //       'status' => "send_result_success",
  //       'status_sequence' => 0,
  //       'notes' => 'http://10.0.06/SIMRS/web_service/Lis/testResult',
  //       'status_2' => 0,
  //       'created_at' => Carbon::now(),
  //       'updated_at' => null
  //     ]);
  // }
  
  /**
   * Private function for set the human readable transaction id
   * 
   * @param object $request the form or request data. It's only use type param tho.
   * 
   * @return string the generated transaction id label
   */
  private function getTransactionType($request)
  {
    $prefix = '';
    switch ($request) {
      case 'rawat_jalan':
        $prefix = 'RWJ';
        break;
      case 'rawat_inap':
        $prefix = 'RWI';
        break;
      case 'igd':
        $prefix = 'IGD';
        break;
      case 'rujukan':
        $prefix = 'RJK';
        break;
      default:
        $prefix = 'TRX';
    }

    $year = date('Y');
    $countExistingData = \App\Transaction::where('transaction_id_label', 'like', $prefix . $year . '%')->count();
    $countExistingData += 1;

    $trxId = str_pad($countExistingData, 7, '0', STR_PAD_LEFT);
    return $prefix . $year . $trxId;
  }
}
