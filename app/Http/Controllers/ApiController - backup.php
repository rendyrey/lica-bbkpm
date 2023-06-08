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

        // check no_order in log data
        // if doesnt exist, dont insert into log data
        $check_exist_failed = DB::table('log_integrations')->select('log_integrations.*')->where('no_order', $transaksi['no_order'])->where('status', 'insert_transaction_failed')->first();

        if (empty($check_exist_failed)) {
            DB::table('log_integrations')
                ->insert([
                    'no_order' => $transaksi['no_order'],
                    'return_result' => json_encode($store_json),
                    'timestamp' => Carbon::now(),
                    'type' => "POST DATA",
                    'status' => "insert_transaction_failed",
                    'status_sequence' => 0,
                    'notes' => "Ruangan (" . $transaksi['ruangan'] . ") tidak terdaftar pada data master LICA",
                    'status_2' => 0,
                    'created_at' => Carbon::now()
                ]);
        }
        return $this->responseWithError(400, "Ruangan (" . $transaksi['ruangan'] . ") tidak terdaftar pada data master LICA");

      }

      // Insert Doctors
      $query = DB::table('doctors')->where('general_code', $transaksi['kode_dokter']);
      $doctor_data = $query->first();
      if (empty($doctor_data)) {
        DB::rollback();

        // check no_order in log data
        // if doesnt exist, dont insert into log data
        $check_exist_failed = DB::table('log_integrations')->select('log_integrations.*')->where('no_order', $transaksi['no_order'])->where('status', 'insert_transaction_failed')->first();

        if (empty($check_exist_failed)) {
            DB::table('log_integrations')
                ->insert([
                    'no_order' => $transaksi['no_order'],
                    'return_result' => json_encode($store_json),
                    'timestamp' => Carbon::now(),
                    'type' => "POST DATA",
                    'status' => "insert_transaction_failed",
                    'status_sequence' => 0,
                    'notes' => "Ruangan (" . $transaksi['ruangan'] . ") tidak terdaftar pada data master LICA",
                    'status_2' => 0,
                    'created_at' => Carbon::now()
                ]);
        }
        return $this->responseWithError(400, "Dokter (" . $transaksi['dokter'] . ") tidak terdaftar pada data master LICA");

      }

      // Insert Insurances
      $query = DB::table('insurances')->where('general_code', $transaksi['kode_pembayaran']);
      $insurance_data = $query->first();
      if (empty($insurance_data)) {
        DB::rollback();

        // check no_order in log data
        // if doesnt exist, dont insert into log data
        $check_exist_failed = DB::table('log_integrations')->select('log_integrations.*')->where('no_order', $transaksi['no_order'])->where('status', 'insert_transaction_failed')->first();

        if (empty($check_exist_failed)) {
            DB::table('log_integrations')
                ->insert([
                    'no_order' => $transaksi['no_order'],
                    'return_result' => json_encode($store_json),
                    'timestamp' => Carbon::now(),
                    'type' => "POST DATA",
                    'status' => "insert_transaction_failed",
                    'status_sequence' => 0,
                    'notes' => "Ruangan (" . $transaksi['pembayaran'] . ") tidak terdaftar pada data master LICA",
                    'status_2' => 0,
                    'created_at' => Carbon::now()
                ]);
        }
        return $this->responseWithError(400, "Jenis Pembayaran/Asuransi (" . $transaksi['pembayaran'] . ") tidak terdaftar pada data master LICA");

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
                          'check_code' => $value['kode_pemeriksaan'],
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
                          'check_code' => $value['kode_pemeriksaan'],
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
              
              // check no_order in log data
              // if doesnt exist, dont insert into log data
              $check_exist_failed = DB::table('log_integrations')->select('log_integrations.*')->where('no_order', $transaksi['no_order'])->where('status', 'insert_transaction_failed')->first();

              if (empty($check_exist_failed)) {
                  DB::table('log_integrations')
                      ->insert([
                          'no_order' => $transaksi['no_order'],
                          'return_result' => json_encode($store_json),
                          'timestamp' => Carbon::now(),
                          'type' => "POST DATA",
                          'status' => "insert_transaction_failed",
                          'status_sequence' => 0,
                          'notes' => "Data tes " . $value['nama_tes'] . " tidak terdaftar pada master data LICA",
                          'status_2' => 0
                      ]);
              }

              // delete transactions record
              DB::table('transactions')->where('id', $transaction_id)->delete();
              // delete transaction_tests record
              DB::table('transaction_tests')->where('transaction_id', $transaction_id)->delete();

              return $this->responseWithError(400, "Data tes " . $value['nama_tes'] . " tidak terdaftar pada master data LICA");
            }else{
              
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
            ]);

        DB::table('log_integrations')->where('no_order', $transaksi['no_order'])->where('status_2', '=', 0)->delete();

        DB::commit();
        return $this->successResponse();

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

      $tests = DB::table('finish_transaction_tests')->where('finish_transaction_id', $transaction_data->id)->get();
      $test_result = [];
      foreach ($tests as $val => $test) {
        // $sub_group_insensitive = strtoupper($sub_group);

        // if ($sub_group_insensitive == "DARAH LENGKAP 4 PARAMETER") {
        //   $count_darah_lengkap = $count_darah_lengkap + 1;
        // }
        $test_result[$val]['flag'] = $test->result_status_label;
        $test_result[$val]['unit'] = $test->unit;
        $test_result[$val]['result'] = $test->global_result;
        $test_result[$val]['test_id'] = $test->test_id;
        $test_result[$val]['test_name'] = $test->test_name;
        $test_result[$val]['group_name'] = $test->group_name;
        $test_result[$val]['nilai_normal'] = $test->normal_value;
        $test_result[$val]['notes'] = $test->memo_test;
      }

      $data['no_ref'] = $transaction_data->no_order;
      //$data['tgl_kirim'] = date("Y-m-d H:i:s");
      $data['no_lab'] = $transaction_data->no_lab;
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
          'status' => "insertpatient",
        ]);
      return $this->responseWithError(401, "Autentikasi tidak valid");
    }
  }

  public function sendResult($transaction_id)
  {

    $url = url('api/get_result/' . $transaction_id);
    $query = DB::table('master_auth_api')->where('api', 'simrs');
    $api_key = $query->first();
    $key = $api_key->key;

    // print_r($url);
    $ch = curl_init($url);
    $header = array(
      'Content-Type: application/json',
      'x-api-key: ' . $key
    );
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_POST, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $result = curl_exec($ch);

    $endpoints = DB::table('master_result_integration')->get();
    foreach ($endpoints as $endpoint) {
      $curl = curl_init($endpoint->url);
      $header = [];
      $parameters = DB::table('master_parameter_integration')->where('id_integration', $endpoint->id)->get();
      foreach ($parameters as $param) {
        $header[] = $param->parameter_name . ': ' . $param->parameter_value;
      }

      curl_setopt($curl, CURLOPT_POST, 1);
      curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

      curl_setopt($curl, CURLOPT_POSTFIELDS, $result);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

      $send_return = curl_exec($curl);
      $result_data = json_decode($result);
      DB::table('log_integrations')
        ->insert([
          'created_at' => Carbon::now(),
          'no_order' => $result_data->no_ref,
          'return_result' => $send_return,
          'timestamp' => Carbon::now(),
          'status' => "sendresult",
          'notes' => $endpoint->url,
        ]);
    }
  }

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
