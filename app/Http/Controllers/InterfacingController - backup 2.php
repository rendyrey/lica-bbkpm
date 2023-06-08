<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class InterfacingController extends Controller
{
    public function insert(Request $request)
    {
        $results = $request->input("lists");

        if ($results != null) {
            //foreach but data only one
            foreach ($results as $result) {
                $lab_no = $result['patientId'];
                $analyzer_id = $result['analyzerId'];
                // $transaction = DB::table('transactions')->where('no_lab', $lab_no)->first();
                $transaction = DB::table('transactions')
                    ->select('patients.gender', 'patients.birthdate', 'transactions.*', 'rooms.auto_draw')
                    ->leftJoin('patients', 'transactions.patient_id', '=', 'patients.id')
                    ->leftJoin('rooms', 'transactions.patient_id', '=', 'patients.id')
                    ->where('no_lab', $lab_no)->first();

                if ($transaction) {
                    $born = Carbon::createFromFormat('Y-m-d', $transaction->birthdate);
                    $ageInDays = Carbon::createFromFormat('Y-m-d', $transaction->birthdate)->diffInDays(Carbon::now());
                    $birthdate = $born->diff(Carbon::now())->format('%yY / %mM / %dD');
                    $birthday = $born->diff(Carbon::now())->days;

                    $transaction_id = $transaction->id;


                    foreach ($result['results'] as $test) {

                        // $interfacing_test = DB::table('interfacings')->where('code', $test['name'])->where('analyzer_id', $analyzer_id)->first();
                        $interfacing_test = DB::table('interfacings')
                            ->select('interfacings.*', 'transaction_tests.test_id')
                            ->leftJoin('transaction_tests', 'interfacings.test_id', '=', 'transaction_tests.test_id')
                            ->where('transaction_tests.transaction_id', $transaction_id)
                            ->where('interfacings.code', $test['name'])
                            ->where('interfacings.analyzer_id', $analyzer_id)->first();

                        // if ($interfacing_test != null) {

                        $test_id = $interfacing_test->test_id;
                        $result_value = $test['result'];

                        //hardcode handle for previous version ok jenis kelamin/gender
                        if ($transaction->gender == "L") {
                            $transaction->gender = "M";
                        } elseif ($transaction->gender == "P") {
                            $transaction->gender = "F";
                        }

                        $range = \App\Range::where('test_id', $test_id)->where('min_age', '<=', $ageInDays)->where('max_age', '>=', $ageInDays)->first();

                        if ($range) {
                            $status = $this->checkResultStatus($transaction->gender, $range, $result_value);

                            switch ($status) {
                                case 'normal':
                                    $result_status = AnalyticController::RESULT_STATUS_NORMAL;
                                    break;
                                case 'low':
                                    $result_status = AnalyticController::RESULT_STATUS_LOW;
                                    break;
                                case 'high':
                                    $result_status = AnalyticController::RESULT_STATUS_HIGH;
                                    break;
                                case 'critical':
                                    $result_status = AnalyticController::RESULT_STATUS_CRITICAL;
                                    break;
                                case 'abnormal':
                                    $result_status = AnalyticController::RESULT_STATUS_ABNORMAL;
                                default:
                                    $result_status = 0;
                            }
                            $test = DB::table('tests')->select('tests.*', 'prices.id as price_id')->leftJoin('prices', 'prices.test_id', '=', 'tests.id')->where('tests.id', $test_id)->first();
                            if (!$interfacing_test) {
                                $now = Carbon::now();
                                $data_test = array(
                                    "transaction_id" => $transaction_id,
                                    "test_id" => $test_id,
                                    "price_id" => $test->price_id,
                                    "group_id" => $test->group_id,
                                    "type" => 'single', //from interfacing test always single
                                    "package_id" => NULL,
                                    "input_time" => $now,
                                );
                                if ($transaction->auto_draw == 1) {
                                    $data_test['draw'] = true;
                                    $data_test['draw_time'] = $now;
                                }
                            }

                            $check_format_number = \App\Test::where('id', $test_id)->first();
                            $format_number = $check_format_number->format_decimal;

                            /*check type*/
                            if ($test->range_type == 'number') {
                                if ($interfacing_test) {

                                    // check format number
                                    if ($format_number != NULL) {
                                        if ($format_number == 1) {
                                            if ($result_value != '') {
                                                $result = number_format($result_value, 1, '.', ',');
                                            } else {
                                                $result = $result_value;
                                            }
                                        } elseif ($format_number == 2) {
                                            if ($result_value != '') {
                                                $result = number_format($result_value, 2, '.', ',');
                                            } else {
                                                $result = $result_value;
                                            }
                                        } elseif ($format_number == 3) {
                                            if ($result_value != '') {
                                                $result = number_format($result_value, 3, '.', ',');
                                            } else {
                                                $result = $result_value;
                                            }
                                        } elseif ($format_number == 4) {
                                            if ($result_value != '') {
                                                $result = number_format($result_value, 4, '.', ',');
                                            } else {
                                                $result = $result_value;
                                            }
                                        } elseif ($format_number == 404) {
                                            if (strpos($result_value, ".") !== false) {
                                                $result = $result_value;
                                            } else {
                                                // ribuan
                                                $result_value = number_format($result_value);
                                                $result = $result_value;
                                            }
                                        }
                                    } else {

                                        if (strlen($result_value) >= 4) {
                                            // bukan ribuan
                                            if (strpos($result_value, ".") !== false) {
                                                $result = (int)$result_value;
                                                $result = number_format($result);
                                            } else {

                                                if (strpos($result_value, ".") !== false) {
                                                    $result = $result_value;
                                                } else {
                                                    // ribuan
                                                    $result_value = number_format($result_value);
                                                    $result = $result_value;
                                                }
                                            }
                                        } else {
                                            if (strpos($result_value, ".") !== false) {
                                                $result = (int)$result_value;
                                            } else {
                                                $result = $result_value;
                                            }
                                        }
                                    }

                                    // echo "Result " . $test->name . "==> " . $result . '<br>';
                                    // echo "Status : " . $result_status . '<hr>';

                                    $interfacing_test = DB::table('transaction_tests')
                                        ->where('test_id', $test_id)
                                        ->where('transaction_id', $transaction_id)
                                        ->where('mark_duplo', 0)
                                        ->update([
                                            'result_number' => $result,
                                            'result_status' => $result_status,
                                            'input_time' => Carbon::now()->toDateTimeString()
                                        ]);
                                } else {
                                    $data_test['result_number'] = $result_value;
                                    $data_test['result_status'] = $result_status;
                                    $interfacing_test = true;
                                }
                            } elseif ($test->range_type == 'label') {
                                // on development

                            }
                        }
                        $interfacing_test = 1;
                        // } else {
                        //     return response()->json('Interfacing Not Found');
                        // }
                    }
                } else {
                    return response()->json('No Lab or patients Not Found');
                }
            }
            return response()->json($interfacing_test);
        } else {
            return response()->json('No Results');
        }
    }

    private function checkResultStatus($gender, $range, $result)
    {
        $status = '';
        if ($gender == 'M') {
            if ($result >= $range->min_male_ref && $result <= $range->max_male_ref) {
                $status = 'normal';
            } else if ($result < $range->min_crit_male || $result > $range->max_crit_male) {
                $status = 'critical';
            } else if ($result < $range->min_male_ref) {
                $status = 'low';
            } else if ($result > $range->max_male_ref) {
                $status = 'high';
            }
        } else {
            if ($result >= $range->min_female_ref && $result <= $range->max_female_ref) {
                $status = 'normal';
            } else if ($result < $range->min_crit_female || $result > $range->max_crit_male) {
                $status = 'critical';
            } else if ($result < $range->min_female_ref) {
                $status = 'low';
            } else if ($result > $range->max_female_ref) {
                $status = 'high';
            }
        }

        return $status;
    }
}
