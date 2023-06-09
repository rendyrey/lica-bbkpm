<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DataTables;
use DB;
use App\FinishTransaction;
use App\FinishTransactionTest;
use Illuminate\Support\Carbon;
use Auth;
use PDF;

class ReportController extends Controller
{
    const STATUS = 2;

    public function selectOptionsType()
    {
        try {
            $data = array("rawat_inap", "rawat_jalan", "igd");

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function selectOptionsPatient()
    {
        try {
            $query = DB::table('patients')->selectRaw('patients.id as patient_id, patients.name as patient_name');
            $data = $query->get();

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function selectOptionsInsurance()
    {
        try {
            $query = DB::table('insurances')->selectRaw('insurances.id as insurance_id, insurances.name as insurance_name');
            $data = $query->get();

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function selectOptionsDoctor()
    {
        try {
            $query = DB::table('doctors')->selectRaw('doctors.id as doctor_id, doctors.name as doctor_name');
            $data = $query->get();

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function selectOptionsTest(Request $request)
    {
        try {
            $query = DB::table('tests')->selectRaw('tests.id as test_id, tests.name as test_name');
            $query = $query->where('name', 'LIKE', '%' . $request->input('query') . '%');
            $data = $query->get();

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    //===============================================================================================
    // CRITICAL REPORT
    //===============================================================================================
    public function criticalReport()
    {
        $data['title'] = 'Critical Report';
        return view('dashboard.report.report_critical.index', $data);
    }

    public function criticalDatatable($startDate = null, $endDate = null, $group_id = null)
    {
        if ($startDate == null && $endDate == null) {
            $from = Carbon::today()->addHours(0)->addMinutes(0)->addSeconds(0)->toDateTimeString();
            $to = Carbon::today()->addHours(23)->addMinutes(59)->addSeconds(59)->toDateTimeString();
        } else {
            $from = Carbon::parse($startDate)->addHours(0)->addMinutes(0)->addSeconds(0)->toDateTimeString();
            $to = Carbon::parse($endDate)->addHours(23)->addMinutes(59)->addSeconds(59)->toDateTimeString();
        }

        $model = \App\FinishTransactionTest::selectRaw('finish_transaction_tests.*, finish_transactions.patient_name, finish_transactions.patient_medrec, finish_transactions.patient_birthdate, finish_transactions.doctor_name');

        $model->leftJoin('finish_transactions', 'finish_transaction_tests.finish_transaction_id', '=', 'finish_transactions.id');
        if ($group_id != null && $group_id != "null" && $group_id != 0) {
            $model->where('finish_transaction_tests.group_id', '=', $group_id);
        }
        $model->where('finish_transaction_tests.input_time', '>=', $from);
        $model->where('finish_transaction_tests.input_time', '<=', $to);
        $model->where('finish_transaction_tests.report_status', '=', 1);
        $model->orderBy('finish_transaction_tests.input_time', 'desc');

        return DataTables::of($model)
            ->addIndexColumn()
            ->escapeColumns([])
            ->make(true);
    }

    public function criticalPrint($startDate = null, $endDate = null, $group_id = null)
    {
        // if the startDate and the endDate not set, the query will be only for today's transactions
        if ($startDate == null && $endDate == null) {
            $from = Carbon::today()->addHours(0)->addMinutes(0)->addSeconds(0)->toDateTimeString();
            $to = Carbon::today()->addHours(23)->addMinutes(59)->addSeconds(59)->toDateTimeString();
        } else {
            $from = Carbon::parse($startDate)->addHours(0)->addMinutes(0)->addSeconds(0)->toDateTimeString();
            $to = Carbon::parse($endDate)->addHours(23)->addMinutes(59)->addSeconds(59)->toDateTimeString();
        }

        $query = DB::table('finish_transaction_tests')->select('finish_transaction_tests.*', 'finish_transactions.patient_name', 'finish_transactions.patient_medrec', 'finish_transactions.patient_birthdate', 'finish_transactions.doctor_name');
        $query->leftJoin('finish_transactions', 'finish_transaction_tests.finish_transaction_id', '=', 'finish_transactions.id');
        if ($group_id != null && $group_id != "null" && $group_id != 0) {
            $query->where('finish_transaction_tests.group_id', '=', $group_id);
        }
        $query->where('finish_transaction_tests.input_time', '>=', $from);
        $query->where('finish_transaction_tests.input_time', '<=', $to);
        $query->where('finish_transaction_tests.report_status', '=', 1);
        $query->orderBy('finish_transaction_tests.input_time', 'desc');

        $data["criticalData"] = $query->get();

        if ($group_id != null && $group_id != "null" && $group_id != 0) {
            $query_group = DB::table('groups')->select('groups.*')->where('id', $group_id);
            $group = $query_group->first();
            $data["group"] = $group->name;
        } else {
            $data["group"] = '-';
        }

        if ($startDate != null && $endDate != null) {
            $data["startDate"] = date('d/m/Y', strtotime($startDate));
            $data["endDate"] = date('d/m/Y', strtotime($endDate));
        } else {
            $data["startDate"] = '-';
            $data["endDate"] = '-';
        }

        return view('dashboard.report.report_critical.print', $data);
    }

    //===============================================================================================
    // DUPLO REPORT
    //===============================================================================================
    public function duploReport()
    {
        $data['title'] = 'Duplo Report';
        return view('dashboard.report.report_duplo.index', $data);
    }

    public function duploDatatable($startDate = null, $endDate = null, $group_id = null)
    {
        if ($startDate == null && $endDate == null) {
            $from = Carbon::today()->addHours(0)->addMinutes(0)->addSeconds(0)->toDateTimeString();
            $to = Carbon::today()->addHours(23)->addMinutes(59)->addSeconds(59)->toDateTimeString();
        } else {
            $from = Carbon::parse($startDate)->addHours(0)->addMinutes(0)->addSeconds(0)->toDateTimeString();
            $to = Carbon::parse($endDate)->addHours(23)->addMinutes(59)->addSeconds(59)->toDateTimeString();
        }

        $model = DB::table('finish_transaction_tests')->selectRaw('finish_transaction_tests.*, finish_transactions.patient_name, finish_transactions.patient_medrec, finish_transactions.patient_birthdate, finish_transactions.patient_gender, MAX(CASE WHEN finish_transaction_tests.mark_duplo = 1 THEN result_number END) "result", MAX(CASE WHEN finish_transaction_tests.mark_duplo = 2 THEN result_number END) "result_duplo"');
        $model->leftJoin('finish_transactions', 'finish_transaction_tests.finish_transaction_id', '=', 'finish_transactions.id');
        if ($group_id != null && $group_id != "null" && $group_id != 0) {
            $model->where('finish_transaction_tests.group_id', '=', $group_id);
        }
        $model->where('finish_transaction_tests.input_time', '>=', $from);
        $model->where('finish_transaction_tests.input_time', '<=', $to);
        $model->where('finish_transaction_tests.mark_duplo', '!=', 0);
        $model->orderBy('finish_transaction_tests.input_time', 'desc');
        $model->groupBy('finish_transaction_tests.finish_transaction_id');

        return DataTables::of($model)
            ->addIndexColumn()
            ->escapeColumns([])
            ->make(true);
    }

    public function duploPrint($startDate = null, $endDate = null, $group_id = null)
    {
        // if the startDate and the endDate not set, the query will be only for today's transactions
        if ($startDate == null && $endDate == null) {
            $from = Carbon::today()->addHours(0)->addMinutes(0)->addSeconds(0)->toDateTimeString();
            $to = Carbon::today()->addHours(23)->addMinutes(59)->addSeconds(59)->toDateTimeString();
        } else {
            $from = Carbon::parse($startDate)->addHours(0)->addMinutes(0)->addSeconds(0)->toDateTimeString();
            $to = Carbon::parse($endDate)->addHours(23)->addMinutes(59)->addSeconds(59)->toDateTimeString();
        }

        $query = DB::table('finish_transaction_tests')->selectRaw('finish_transaction_tests.*, finish_transactions.patient_name, finish_transactions.patient_medrec, finish_transactions.patient_birthdate, finish_transactions.patient_gender, MAX(CASE WHEN finish_transaction_tests.mark_duplo = 1 THEN global_result END) "result", MAX(CASE WHEN finish_transaction_tests.mark_duplo = 2 THEN global_result END) "result_duplo"');
        $query->leftJoin('finish_transactions', 'finish_transaction_tests.finish_transaction_id', '=', 'finish_transactions.id');
        if ($group_id != null && $group_id != "null" && $group_id != 0) {
            $query->where('finish_transaction_tests.group_id', '=', $group_id);
        }
        $query->where('finish_transaction_tests.input_time', '>=', $from);
        $query->where('finish_transaction_tests.input_time', '<=', $to);
        $query->where('finish_transaction_tests.mark_duplo', '!=', 0);
        $query->orderBy('finish_transaction_tests.input_time', 'desc');
        $query->groupBy('finish_transaction_tests.finish_transaction_id');

        $data["duploData"] = $query->get();

        if ($group_id != null && $group_id != "null" && $group_id != 0) {
            $query_group = DB::table('groups')->select('groups.*')->where('id', $group_id);
            $group = $query_group->first();
            $data["group"] = $group->name;
        } else {
            $data["group"] = '-';
        }

        if ($startDate != null && $endDate != null) {
            $data["startDate"] = date('d/m/Y', strtotime($startDate));
            $data["endDate"] = date('d/m/Y', strtotime($endDate));
        } else {
            $data["startDate"] = '-';
            $data["endDate"] = '-';
        }

        return view('dashboard.report.report_duplo.print', $data);
    }

    //===============================================================================================
    // GROUP TEST REPORT
    //===============================================================================================
    public function groupTestReport()
    {
        $data['title'] = 'Group Test Report';
        return view('dashboard.report.report_group_test.index', $data);
    }

    public function groupTestDatatable($startDate = null, $endDate = null, $group_id = null)
    {
        if ($startDate == null && $endDate == null) {
            $from = Carbon::today()->addHours(0)->addMinutes(0)->addSeconds(0)->toDateTimeString();
            $to = Carbon::today()->addHours(23)->addMinutes(59)->addSeconds(59)->toDateTimeString();
        } else {
            $from = Carbon::parse($startDate)->addHours(0)->addMinutes(0)->addSeconds(0)->toDateTimeString();
            $to = Carbon::parse($endDate)->addHours(23)->addMinutes(59)->addSeconds(59)->toDateTimeString();
        }

        $model = \App\FinishTransactionTest::selectRaw('finish_transaction_tests.id, finish_transaction_tests.group_name, COUNT(finish_transaction_tests.group_id) as total_test')
            ->where('input_time', '>=', $from)
            ->where('input_time', '<=', $to);
        if ($group_id != null && $group_id != "null" && $group_id != 0) {
            $model->where('group_id', $group_id);
        }
        $model->groupBy('group_id');

        return DataTables::of($model)
            ->addIndexColumn()
            ->escapeColumns([])
            ->make(true);

        // query backup
        // $query = DB::table('finish_transactions')->selectRaw('finish_transactions.*, finish_transaction_tests.group_name');

        // $query->leftJoin('finish_transaction_tests', 'finish_transactions.id', '=', 'finish_transaction_tests.finish_transaction_id');
        // if ($group_id != null && $group_id != "null" && $group_id != 0) {
        //     $query->where('finish_transaction_tests.group_id', '=', $group_id);
        // }
        // $query->where('finish_transactions.created_time', '>=', $from);
        // $query->where('finish_transactions.created_time', '<=', $to);
        // $query->orderBy('finish_transactions.created_time', 'desc');
        // $query->groupBy('finish_transactions.id');

        // $group_data = $query->get();

        // return response()->json($group_data);

        // query backup
        // $model = DB::table('finish_transactions')->selectRaw('finish_transactions.*, finish_transaction_tests.group_name');

        // $model->leftJoin('finish_transaction_tests', 'finish_transactions.id', '=', 'finish_transaction_tests.finish_transaction_id');
        // if ($group_id != null && $group_id != "null" && $group_id != 0) {
        //     $model->where('finish_transaction_tests.group_id', '=', $group_id);
        // }
        // $model->where('finish_transactions.created_time', '>=', $from);
        // $model->where('finish_transactions.created_time', '<=', $to);
        // $model->orderBy('finish_transactions.created_time', 'desc');
        // $model->groupBy('finish_transactions.id');

        // return DataTables::of($model)
        //     ->addIndexColumn()
        //     ->escapeColumns([])
        //     ->make(true);
    }

    public function groupTestPrint($startDate = null, $endDate = null, $group_id = null)
    {
        // if the startDate and the endDate not set, the query will be only for today's transactions
        if ($startDate == null && $endDate == null) {
            $from = Carbon::today()->addHours(0)->addMinutes(0)->addSeconds(0)->toDateTimeString();
            $to = Carbon::today()->addHours(23)->addMinutes(59)->addSeconds(59)->toDateTimeString();
        } else {
            $from = Carbon::parse($startDate)->addHours(0)->addMinutes(0)->addSeconds(0)->toDateTimeString();
            $to = Carbon::parse($endDate)->addHours(23)->addMinutes(59)->addSeconds(59)->toDateTimeString();
        }

        $query = DB::table('finish_transaction_tests')->selectRaw('finish_transaction_tests.id, finish_transaction_tests.group_name, COUNT(finish_transaction_tests.group_id) as total_test')
            ->where('input_time', '>=', $from)
            ->where('input_time', '<=', $to);
        if ($group_id != null && $group_id != "null" && $group_id != 0) {
            $query->where('group_id', $group_id);
        }
        $query->groupBy('group_id');
        $data["groupData"] = $query->get();

        if ($group_id != null && $group_id != "null" && $group_id != 0) {
            $query_group = DB::table('groups')->select('name as group_name');
            $group = $query_group->first();
            $data["group"] = $group->group_name;
        } else {
            $data["group"] = '-';
        }

        if ($startDate != null && $endDate != null) {
            $data["startDate"] = date('d/m/Y', strtotime($startDate));
            $data["endDate"] = date('d/m/Y', strtotime($endDate));
        } else {
            $data["startDate"] = '-';
            $data["endDate"] = '-';
        }

        // $query_transactions = DB::table('finish_transactions')->selectRaw('finish_transactions.*, finish_transaction_tests.group_name');
        // $query_transactions->leftJoin('finish_transaction_tests', 'finish_transactions.id', '=', 'finish_transaction_tests.finish_transaction_id');
        // $query_transactions->where('finish_transactions.created_time', '>=', $from);
        // $query_transactions->where('finish_transactions.created_time', '<=', $to);
        // if ($group_id != null && $group_id != "null" && $group_id != 0) {
        //     $query_transactions->where('finish_transaction_tests.group_id', $group_id);
        // }
        // $query_transactions->orderBy('finish_transactions.created_time', 'desc');
        // $query_transactions->groupBy('finish_transaction_tests.group_id');


        return view('dashboard.report.report_group_test.print', $data);
    }

    //===============================================================================================
    // TAT (TURNAROUND TIME) REPORT
    //===============================================================================================
    public function TATReport()
    {
        $data['title'] = 'Turnaround Time Report';
        return view('dashboard.report.report_tat.index', $data);
    }

    public function TATDatatable($startDate = null, $endDate = null, $group_id = null)
    {
        if ($startDate == null && $endDate == null) {
            $from = Carbon::today()->addHours(0)->addMinutes(0)->addSeconds(0)->toDateTimeString();
            $to = Carbon::today()->addHours(23)->addMinutes(59)->addSeconds(59)->toDateTimeString();
        } else {
            $from = Carbon::parse($startDate)->addHours(0)->addMinutes(0)->addSeconds(0)->toDateTimeString();
            $to = Carbon::parse($endDate)->addHours(23)->addMinutes(59)->addSeconds(59)->toDateTimeString();
        }

        $model = DB::table('finish_transactions')->selectRaw('finish_transactions.*, finish_transaction_tests.group_id, finish_transaction_tests.group_name, finish_transaction_tests.package_name, finish_transaction_tests.draw_time, finish_transaction_tests.validate_time');
        $model->leftJoin('finish_transaction_tests', 'finish_transactions.id', '=', 'finish_transaction_tests.finish_transaction_id');
        $model->where('finish_transactions.created_time', '>=', $from);
        $model->where('finish_transactions.created_time', '<=', $to);
        if ($group_id != null && $group_id != "null" && $group_id != 0) {
            $model->where('group_id', '=', $group_id);
        }
        $model->orderBy('finish_transactions.created_time', 'desc');
        $model->groupBy('finish_transactions.id');
        $model->groupBy('finish_transaction_tests.group_id');
        $tatData = $model->get();
        // echo '<pre>';
        // print_r($tatData);
        // die;

        $packageNameArray = [];
        $testNameArray = [];
        foreach ($tatData as $key => $value) {
            $packageNameArray[$value->group_name] = [];
            $testNameArray[$value->group_name] = [];

            $query_package = DB::table('finish_transaction_tests')->selectRaw('finish_transaction_id, group_id, package_name')
                ->where('package_id', '!=', NULL)
                ->where('group_id', $value->group_id)
                ->where('transaction_id', $value->transaction_id);
            $packageData = $query_package->get();

            $query_single_test = DB::table('finish_transaction_tests')->selectRaw('finish_transaction_id, group_id, test_name')
                ->where('package_id', '=', NULL)
                ->where('group_id', $value->group_id)
                ->where('transaction_id', $value->transaction_id);
            $testData = $query_single_test->get();

            // echo '<pre>';
            // print_r($testData);

            // package name 
            if ($packageData) {
                foreach ($packageData as $packageData) {
                    if ($value->group_id == $packageData->group_id) {
                        // echo $test_data->group_id . '<br>';
                        array_push($packageNameArray[$value->group_name], $packageData->package_name);
                    }
                }
            }
            if ($testData) {
                foreach ($testData as $test_data) {
                    if ($value->group_id == $test_data->group_id) {
                        // echo $test_data->group_id . '<br>';
                        array_push($testNameArray[$value->group_name], $test_data->test_name);
                    }
                }
            }

            // echo '<pre>';
            // print_r($testNameArray[$value->group_name]);
            $testNameString = implode(", ", $testNameArray[$value->group_name]);
            // echo  $testNameString . '<br>';
            $tatData[$key]->package_name_custom = $packageNameArray;
            $tatData[$key]->test_name_custom = $testNameString;
        }

        // echo '<pre>';
        // print_r($tatData);
        // die;

        return DataTables::of($tatData)
            ->addIndexColumn()
            ->escapeColumns([])
            ->make(true);
    }

    public function TATPrint($startDate = null, $endDate = null, $group_id = null)
    {
        // if the startDate and the endDate not set, the query will be only for today's transactions
        if ($startDate == null && $endDate == null) {
            $from = Carbon::today()->addHours(0)->addMinutes(0)->addSeconds(0)->toDateTimeString();
            $to = Carbon::today()->addHours(23)->addMinutes(59)->addSeconds(59)->toDateTimeString();
        } else {
            $from = Carbon::parse($startDate)->addHours(0)->addMinutes(0)->addSeconds(0)->toDateTimeString();
            $to = Carbon::parse($endDate)->addHours(23)->addMinutes(59)->addSeconds(59)->toDateTimeString();
        }

        $model = DB::table('finish_transactions')->selectRaw('finish_transactions.*, finish_transaction_tests.group_id, finish_transaction_tests.group_name, finish_transaction_tests.package_name, finish_transaction_tests.draw_time, finish_transaction_tests.validate_time');
        $model->leftJoin('finish_transaction_tests', 'finish_transactions.id', '=', 'finish_transaction_tests.finish_transaction_id');
        $model->where('finish_transactions.created_time', '>=', $from);
        $model->where('finish_transactions.created_time', '<=', $to);
        if ($group_id != null && $group_id != "null" && $group_id != 0) {
            $model->where('group_id', '=', $group_id);
        }
        $model->orderBy('finish_transactions.created_time', 'desc');
        $model->groupBy('finish_transactions.id');
        $model->groupBy('finish_transaction_tests.group_id');
        $tatData = $model->get();
        // echo '<pre>';
        // print_r($tatData);
        // die;

        $packageNameArray = [];
        $testNameArray = [];
        foreach ($tatData as $key => $value) {
            $packageNameArray[$value->group_name] = [];
            $testNameArray[$value->group_name] = [];

            $query_package = DB::table('finish_transaction_tests')->selectRaw('finish_transaction_id, group_id, package_name')
                ->where('package_id', '!=', NULL)
                ->where('group_id', $value->group_id)
                ->where('transaction_id', $value->transaction_id);
            $packageData = $query_package->get();

            $query_single_test = DB::table('finish_transaction_tests')->selectRaw('finish_transaction_id, group_id, test_name')
                ->where('package_id', '=', NULL)
                ->where('group_id', $value->group_id)
                ->where('transaction_id', $value->transaction_id);
            $testData = $query_single_test->get();

            // echo '<pre>';
            // print_r($testData);

            // package name 
            if ($packageData) {
                foreach ($packageData as $packageData) {
                    if ($value->group_id == $packageData->group_id) {
                        // echo $test_data->group_id . '<br>';
                        array_push($packageNameArray[$value->group_name], $packageData->package_name);
                    }
                }
            }
            if ($testData) {
                foreach ($testData as $test_data) {
                    if ($value->group_id == $test_data->group_id) {
                        // echo $test_data->group_id . '<br>';
                        array_push($testNameArray[$value->group_name], $test_data->test_name);
                    }
                }
            }

            // echo '<pre>';
            // print_r($testNameArray[$value->group_name]);
            $testNameString = implode(", ", $testNameArray[$value->group_name]);
            // echo  $testNameString . '<br>';
            $tatData[$key]->package_name_custom = $packageNameArray;
            $tatData[$key]->test_name_custom = $testNameString;
        }

        $data["tatData"] = $tatData;

        if ($group_id != null && $group_id != "null" && $group_id != 0) {
            $query_group = DB::table('groups')->select('name as group_name');
            $group = $query_group->first();
            $data["group"] = $group->group_name;
        } else {
            $data["group"] = '-';
        }

        if ($startDate != null && $endDate != null) {
            $data["startDate"] = date('d/m/Y', strtotime($startDate));
            $data["endDate"] = date('d/m/Y', strtotime($endDate));
        } else {
            $data["startDate"] = '-';
            $data["endDate"] = '-';
        }

        return view('dashboard.report.report_tat.print', $data);
    }

    //===============================================================================================
    // PATIENT REPORT
    //===============================================================================================
    public function patientReport()
    {
        $data['title'] = 'Patient Report';
        return view('dashboard.report.report_patient.index', $data);
    }

    public function patientDatatable($startDate = null, $endDate = null, $type = null)
    {
        if ($startDate == null && $endDate == null) {
            $from = Carbon::today()->addHours(0)->addMinutes(0)->addSeconds(0)->toDateTimeString();
            $to = Carbon::today()->addHours(23)->addMinutes(59)->addSeconds(59)->toDateTimeString();
        } else {
            $from = Carbon::parse($startDate)->addHours(0)->addMinutes(0)->addSeconds(0)->toDateTimeString();
            $to = Carbon::parse($endDate)->addHours(23)->addMinutes(59)->addSeconds(59)->toDateTimeString();
        }

        $model = DB::table('finish_transactions')->selectRaw('finish_transactions.*');
        $model->where('created_time', '>=', $from);
        $model->where('created_time', '<=', $to);
        if ($type != null && $type != "null" && $type != 0) {
            $model->where('type', '=', $type);
        }
        $model->orderBy('created_time', 'desc');

        return DataTables::of($model)
            ->addIndexColumn()
            ->escapeColumns([])
            ->make(true);
    }

    public function patientPrint($startDate = null, $endDate = null, $type = null)
    {
        // if the startDate and the endDate not set, the query will be only for today's transactions
        if ($startDate == null && $endDate == null) {
            $from = Carbon::today()->addHours(0)->addMinutes(0)->addSeconds(0)->toDateTimeString();
            $to = Carbon::today()->addHours(23)->addMinutes(59)->addSeconds(59)->toDateTimeString();
        } else {
            $from = Carbon::parse($startDate)->addHours(0)->addMinutes(0)->addSeconds(0)->toDateTimeString();
            $to = Carbon::parse($endDate)->addHours(23)->addMinutes(59)->addSeconds(59)->toDateTimeString();
        }

        $query = DB::table('finish_transactions')->selectRaw('finish_transactions.*');
        $query->where('created_time', '>=', $from);
        $query->where('created_time', '<=', $to);
        if ($type != null && $type != "null" && $type != 0) {
            $query->where('type', $type);
        }
        $query->orderBy('created_time', 'desc');

        $data["patientData"] = $query->get();

        if ($type != null && $type != "null" && $type != 0) {
            if ($type == 'rawat_inap') {
                $data["type"] = 'Rawat Inap';
            } else if ($type == 'rawat_jalan') {
                $data["type"] = 'Rawat Jalan';
            } else {
                $data["type"] = 'IGD';
            }
        } else {
            $data["type"] = '-';
        }

        if ($startDate != null && $endDate != null) {
            $data["startDate"] = date('d/m/Y', strtotime($startDate));
            $data["endDate"] = date('d/m/Y', strtotime($endDate));
        } else {
            $data["startDate"] = '-';
            $data["endDate"] = '-';
        }

        return view('dashboard.report.report_patient.print', $data);
    }

    //===============================================================================================
    // PATIENT DETAIL REPORT
    //===============================================================================================
    public function patientDetailReport()
    {
        $data['title'] = 'Patient Detail Report';
        return view('dashboard.report.report_patient_detail.index', $data);
    }

    public function patientDetailDatatable($startDate = null, $endDate = null, $patient_id = null)
    {
        $query = DB::table('finish_transaction_tests')->select('finish_transaction_tests.finish_transaction_id', 'finish_transaction_tests.input_time', 'finish_transaction_tests.test_name', 'finish_transaction_tests.global_result')
            ->leftJoin('finish_transactions', 'finish_transaction_tests.finish_transaction_id', '=', 'finish_transactions.id')
            ->where('finish_transactions.patient_id', $patient_id);
        $query->whereRaw("date(finish_transaction_tests.input_time) between '" . $startDate . "' and '" . $endDate . "'");

        return DataTables::of($query)
            ->addIndexColumn()
            ->escapeColumns([])
            ->make(true);
    }

    public function patientDetailPrint($startDate = null, $endDate = null, $patient_id = null)
    {
        $query = DB::table('finish_transaction_tests')->select('finish_transaction_tests.finish_transaction_id', 'finish_transaction_tests.input_time', 'finish_transaction_tests.test_name', 'finish_transaction_tests.global_result')
            ->leftJoin('finish_transactions', 'finish_transaction_tests.finish_transaction_id', '=', 'finish_transactions.id')
            ->where('finish_transactions.patient_id', $patient_id);
        $query->whereRaw("date(finish_transaction_tests.input_time) between '" . $startDate . "' and '" . $endDate . "'");

        $data["patientData"] = $query->get();

        if ($patient_id != null && $patient_id != "null" && $patient_id != 0) {
            $query_patient = DB::table('patients')->select('patients.name as patient_name')
                ->where('id', '=', $patient_id);
            $patient = $query_patient->first();
            $data['patient'] = $patient;
        } else {
            $data['patient'] = '';
        }

        if ($startDate != null && $endDate != null) {
            $data["startDate"] = date('d/m/Y', strtotime($startDate));
            $data["endDate"] = date('d/m/Y', strtotime($endDate));
        } else {
            $data["startDate"] = '-';
            $data["endDate"] = '-';
        }

        return view('dashboard.report.report_patient_detail.print', $data);
    }   

    //===============================================================================================
    // TEST DETAIL REPORT
    //===============================================================================================

    public function testDetailReport()
    {
        $data['title'] = 'Test Detail Report';
        return view('dashboard.report.report_test_detail.index', $data);
    }

    public function testDetailDatatable($startDate = null, $endDate = null, $test_id = null)
    {

        if ($startDate == null && $endDate == null) {
            $from = Carbon::today()->addHours(0)->addMinutes(0)->addSeconds(0)->toDateTimeString();
            $to = Carbon::today()->addHours(23)->addMinutes(59)->addSeconds(59)->toDateTimeString();
        } else {
            $from = Carbon::parse($startDate)->addHours(0)->addMinutes(0)->addSeconds(0)->toDateTimeString();
            $to = Carbon::parse($endDate)->addHours(23)->addMinutes(59)->addSeconds(59)->toDateTimeString();
        }

        $model = FinishTransaction::selectRaw('finish_transactions.*');
        if ($test_id != null && $test_id != "null" && $test_id != 0) {
            $model->join('finish_transaction_tests', 'finish_transaction_tests.finish_transaction_id', '=', 'finish_transactions.id')
                ->where('test_id', '=', $test_id);
        }

        $model->where('created_time', '>=', $from);
        $model->where('created_time', '<=', $to);

        return DataTables::of($model)
            ->addIndexColumn()
            ->addColumn('test_names', function($data) use($test_id) {
                if ($test_id != null && $test_id != "null" && $test_id != 0) {
                    $ftt = DB::table('finish_transaction_tests')->select('test_name')->where('finish_transaction_id', $data->id)->where('test_id', $test_id)->get();
                } else {
                    $ftt = DB::table('finish_transaction_tests')->select('test_name')->where('finish_transaction_id', $data->id)->get();
                }

                $names = '';
                foreach($ftt as $key => $value) {
                    $names .= $value->test_name;
                    if ($key < count($ftt) - 1) {
                        $names .= ', ';
                    }
                }
                return $names;
            })
            ->addColumn('test_global_results', function($data) use($test_id) { 
                if ($test_id != null && $test_id != "null" && $test_id != 0) {
                    $ftt = DB::table('finish_transaction_tests')->select('test_name', 'global_result', 'unit')->where('finish_transaction_id', $data->id)->where('test_id', $test_id)->get();
                } else {
                    $ftt = DB::table('finish_transaction_tests')->select('test_name', 'global_result', 'unit')->where('finish_transaction_id', $data->id)->get();
                }
                $global_result = '';
                foreach($ftt as $key => $value) {
                    $global_result .= $value->test_name.": ".$value->global_result." ".$value->unit;
                    if ($key < count($ftt) - 1) {
                        $global_result .= '<br>';
                    }
                }
                return $global_result;
            })
            ->escapeColumns([])
            ->make(true);
    }


    //===============================================================================================
    // TEST REPORT
    //===============================================================================================
    public function testReport()
    {
        $data['title'] = 'Test Report';
        return view('dashboard.report.report_test.index', $data);
    }

    public function testDatatable($startDate = null, $endDate = null, $test_id = null)
    {
        // if the startDate and the endDate not set, the query will be only for today's transactions
        if ($startDate == null && $endDate == null) {
            $from = Carbon::today()->addHours(0)->addMinutes(0)->addSeconds(0)->toDateTimeString();
            $to = Carbon::today()->addHours(23)->addMinutes(59)->addSeconds(59)->toDateTimeString();
        } else {
            $from = Carbon::parse($startDate)->addHours(0)->addMinutes(0)->addSeconds(0)->toDateTimeString();
            $to = Carbon::parse($endDate)->addHours(23)->addMinutes(59)->addSeconds(59)->toDateTimeString();
        }

        $model = \App\FinishTransactionTest::selectRaw('COUNT(finish_transaction_tests.test_id) as total, finish_transaction_tests.test_id as test_id, finish_transaction_tests.test_name as test_name, finish_transaction_tests.input_time')
            ->where('input_time', '>=', $from)
            ->where('input_time', '<=', $to);
        if ($test_id != null && $test_id != "null" && $test_id != 0) {
            $model->where('finish_transaction_tests.test_id', '=', $test_id);
        }
        $model->groupBy('test_id');

        return DataTables::of($model)
            ->addIndexColumn()
            ->escapeColumns([])
            ->make(true);
    }

    public function testPrint($startDate = null, $endDate = null, $test_id = null)
    {
        // if the startDate and the endDate not set, the query will be only for today's transactions
        if ($startDate == null && $endDate == null) {
            $from = Carbon::today()->addHours(0)->addMinutes(0)->addSeconds(0)->toDateTimeString();
            $to = Carbon::today()->addHours(23)->addMinutes(59)->addSeconds(59)->toDateTimeString();
        } else {
            $from = Carbon::parse($startDate)->addHours(0)->addMinutes(0)->addSeconds(0)->toDateTimeString();
            $to = Carbon::parse($endDate)->addHours(23)->addMinutes(59)->addSeconds(59)->toDateTimeString();
        }

        if ($startDate != null && $endDate != null) {
            $data["startDate"] = date('d/m/Y', strtotime($startDate));
            $data["endDate"] = date('d/m/Y', strtotime($endDate));
        } else {
            $data["startDate"] = '-';
            $data["endDate"] = '-';
        }

        $query_package_count = DB::table('finish_transaction_tests')
            ->select('test_id', 'package_id', 'package_name', 'group_id', 'group_name', 'sub_group', 'sequence', 'finish_transactions.insurance_name')
            ->leftJoin('finish_transactions', 'finish_transaction_tests.finish_transaction_id', '=', 'finish_transactions.id')
            ->where('input_time', '>=', $from)
            ->where('input_time', '<=', $to)
            ->where('package_id', '!=', null)
            ->orderBy('sequence', 'asc')
            ->orderBy('sub_group', 'asc')
            ->groupBy('package_id')
            ->groupBy('finish_transaction_tests.transaction_id');
        $package_count_data = $query_package_count->get();

        $query_test_count = DB::table('finish_transaction_tests')
            ->select('package_id', 'test_id', 'test_name', 'package_name', 'group_id', 'group_name', 'sub_group', 'sequence', 'finish_transactions.insurance_name')
            ->leftJoin('finish_transactions', 'finish_transaction_tests.finish_transaction_id', '=', 'finish_transactions.id')
            ->where('input_time', '>=', $from)
            ->where('input_time', '<=', $to)
            ->where('package_id', '=', null)
            ->orderBy('sequence', 'asc')
            ->orderBy('sub_group', 'asc');
        $test_count_data = $query_test_count->get();

        $query_package_display = DB::table('finish_transaction_tests')
            ->select('test_id', 'package_id', 'package_name', 'group_id', 'group_name', 'sub_group', 'sequence')
            ->where('input_time', '>=', $from)
            ->where('input_time', '<=', $to)
            ->where('package_id', '!=', null)
            ->orderBy('sequence', 'asc')
            ->orderBy('sub_group', 'asc')
            ->groupBy('package_id');
        $package_display = $query_package_display->get();

        $query_test_display = DB::table('finish_transaction_tests')
            ->select('package_id', 'test_id', 'test_name', 'package_name', 'group_id', 'group_name', 'sub_group', 'sequence')
            ->where('input_time', '>=', $from)
            ->where('input_time', '<=', $to)
            ->where('package_id', '=', null)
            ->orderBy('sequence', 'asc')
            ->orderBy('sub_group', 'asc')
            ->groupBy('test_id');
        $test_display = $query_test_display->get();

        $query_group = DB::table('finish_transaction_tests')
            ->select('group_name')
            ->where('input_time', '>=', $from)
            ->where('input_time', '<=', $to)
            ->orderBy('sequence', 'asc')
            ->groupBy('group_name')
            ->get();

        $query_subgroup = DB::table('finish_transaction_tests')
            ->selectRaw("CASE WHEN sub_group IS NULL OR sub_group = '' THEN '' ELSE sub_group END as sub_group_grouped")
            ->where('input_time', '>=', $from)
            ->where('input_time', '<=', $to)
            ->groupBy('sub_group_grouped')
            ->get();

        $sorted_test = [];
        $group_array = [];
        $jumlah_package = 0;
        foreach ($query_group as $group) {
            // echo $group->group_name . '<br>';
            $group_array[$group->group_name] = [];
            array_push($group_array[$group->group_name], $group->group_name);

            foreach ($query_subgroup as $subgroup) {
                // package
                foreach ($package_display as $keyPackageDisplay => $valuePackage) {
                    if (($valuePackage->sub_group == $subgroup->sub_group_grouped) && ($valuePackage->group_name == $group->group_name)) {
                        if (($subgroup->sub_group_grouped != '') && (!in_array($subgroup->sub_group_grouped, $group_array[$group->group_name]))) {
                            array_push($group_array[$group->group_name], $subgroup->sub_group_grouped);
                        }

                        // count total package
                        // $package_display[$keyPackageDisplay]->jumlah_package = $jumlah_package++;

                        array_push($group_array[$group->group_name], $valuePackage->package_name);
                        array_push($sorted_test, $valuePackage);
                    }
                }

                // single test
                foreach ($test_display as $test) {
                    if (($test->sub_group == $subgroup->sub_group_grouped) && ($test->group_name == $group->group_name)) {
                        if (($subgroup->sub_group_grouped != '') && (!in_array($subgroup->sub_group_grouped, $group_array[$group->group_name]))) {
                            array_push($group_array[$group->group_name], $subgroup->sub_group_grouped);
                        }
                        array_push($group_array[$group->group_name], $test->test_name);
                        array_push($sorted_test, $test);
                    }
                }
            }
        }

        // inisialisasi jumlah package & single test
        foreach ($sorted_test as $keySort => $valueSort) {
            $sorted_test[$keySort]->jumlah_package_umum = 0;
            $sorted_test[$keySort]->jumlah_package_bpjs = 0;
            $sorted_test[$keySort]->jumlah_test_umum = 0;
            $sorted_test[$keySort]->jumlah_test_bpjs = 0;
        }

        foreach ($test_count_data  as $keyTestCount => $valueTestCount) {
            foreach ($sorted_test as $keySort => $valueSort) {
                if ($valueTestCount->test_id == $valueSort->test_id) {
                    if ($valueTestCount->insurance_name == 'UMUM') {
                        $sorted_test[$keySort]->jumlah_test_umum++;
                    }
                    if ($valueTestCount->insurance_name == 'JKN') {
                        $sorted_test[$keySort]->jumlah_test_bpjs++;
                    }
                }
            }
        }

        foreach ($package_count_data  as $keyPackageCount => $valuePackageCount) {
            foreach ($sorted_test as $keySort => $valueSort) {
                if ($valuePackageCount->package_id == $valueSort->package_id) {
                    if ($valuePackageCount->insurance_name == 'UMUM') {
                        $sorted_test[$keySort]->jumlah_package_umum++;
                    }
                    if ($valuePackageCount->insurance_name == 'JKN') {
                        $sorted_test[$keySort]->jumlah_package_bpjs++;
                    }
                }
            }
        }

        // echo '<pre>';
        // print_r($sorted_test);
        // die;

        $data['testData'] = $sorted_test;

        return view('dashboard.report.report_test.print', $data);
    }

    //===============================================================================================
    // SPECIMEN REPORT
    //===============================================================================================
    public function specimenReport()
    {
        $data['title'] = 'Specimen Report';
        return view('dashboard.report.report_specimen.index', $data);
    }

    public function specimenDatatable($startDate = null, $endDate = null, $specimen_id = null)
    {
        // if the startDate and the endDate not set, the query will be only for today's transactions
        if ($startDate == null && $endDate == null) {
            $from = Carbon::today()->addHours(0)->addMinutes(0)->addSeconds(0)->toDateTimeString();
            $to = Carbon::today()->addHours(23)->addMinutes(59)->addSeconds(59)->toDateTimeString();
        } else {
            $from = Carbon::parse($startDate)->addHours(0)->addMinutes(0)->addSeconds(0)->toDateTimeString();
            $to = Carbon::parse($endDate)->addHours(23)->addMinutes(59)->addSeconds(59)->toDateTimeString();
        }

        $model = \App\FinishTransactionTest::selectRaw('COUNT(finish_transaction_tests.specimen_id) as total, finish_transaction_tests.id, finish_transaction_tests.specimen_id as specimen_id, finish_transaction_tests.specimen_name as specimen_name, finish_transaction_tests.input_time')
            ->where('input_time', '>=', $from)
            ->where('input_time', '<=', $to);
        if ($specimen_id != null && $specimen_id != "null" && $specimen_id != 0) {
            $model->where('finish_transaction_tests.specimen_id', '=', $specimen_id);
        }
        $model->groupBy('transaction_id');
        $model->groupBy('specimen_id');

        // $subQuery = "(SELECT finish_transaction_tests.test_id, specimens.name AS specimen_name, specimens.id AS specimen_id, finish_transaction_tests.transaction_id FROM finish_transaction_tests LEFT JOIN tests ON finish_transaction_tests.test_id = tests.id LEFT JOIN specimens ON tests.specimen_id = specimens.id) AS specimen_data";

        // $model = DB::table('finish_transaction_tests')->selectRaw("specimen_id, specimen_name, COUNT(test_id) as total, $subQuery")
        //     ->groupBy('finish_transaction_tests.transaction_id');


        return DataTables::of($model)
            ->addIndexColumn()
            ->escapeColumns([])
            ->make(true);
    }

    public function specimenPrint($startDate = null, $endDate = null, $specimen_id = null)
    {
        // if the startDate and the endDate not set, the query will be only for today's transactions
        if ($startDate == null && $endDate == null) {
            $from = Carbon::today()->addHours(0)->addMinutes(0)->addSeconds(0)->toDateTimeString();
            $to = Carbon::today()->addHours(23)->addMinutes(59)->addSeconds(59)->toDateTimeString();
        } else {
            $from = Carbon::parse($startDate)->addHours(0)->addMinutes(0)->addSeconds(0)->toDateTimeString();
            $to = Carbon::parse($endDate)->addHours(23)->addMinutes(59)->addSeconds(59)->toDateTimeString();
        }

        $query = \App\FinishTransactionTest::selectRaw('COUNT(finish_transaction_tests.specimen_id) as total, finish_transaction_tests.id, finish_transaction_tests.specimen_id as specimen_id, finish_transaction_tests.specimen_name as specimen_name, finish_transaction_tests.input_time')
            ->where('input_time', '>=', $from)
            ->where('input_time', '<=', $to);
        if ($specimen_id != null && $specimen_id != "null" && $specimen_id != 0) {
            $query->where('finish_transaction_tests.specimen_id', '=', $specimen_id);
        }
        $query->groupBy('transaction_id');
        $query->groupBy('specimen_id');

        $data["specimenData"] = $query->get();

        if ($specimen_id != null && $specimen_id != "null" && $specimen_id != 0) {
            $query_specimen = DB::table('specimens')->select('specimens.*')->where('id', $specimen_id);
            $specimen = $query_specimen->first();
            $data["specimen"] = $specimen->name;
        } else {
            $data["specimen"] = '-';
        }

        if ($startDate != null && $endDate != null) {
            $data["startDate"] = date('d/m/Y', strtotime($startDate));
            $data["endDate"] = date('d/m/Y', strtotime($endDate));
        } else {
            $data["startDate"] = '-';
            $data["endDate"] = '-';
        }

        return view('dashboard.report.report_specimen.print', $data);
    }

    //===============================================================================================
    // PHLEBOTOMY & SAMPLING REPORT
    //===============================================================================================
    public function flebotomiSamplingReport()
    {
        $data['title'] = 'Phlebotomy & Sampling Report';
        return view('dashboard.report.report_flebotomi_sampling.index', $data);
    }

    public function flebotomiSamplingDatatable($startDate = null, $endDate = null, $specimen_id = null)
    {
        if ($startDate == null && $endDate == null) {
            $from = Carbon::today()->addHours(0)->addMinutes(0)->addSeconds(0)->toDateTimeString();
            $to = Carbon::today()->addHours(23)->addMinutes(59)->addSeconds(59)->toDateTimeString();
        } else {
            $from = Carbon::parse($startDate)->addHours(0)->addMinutes(0)->addSeconds(0)->toDateTimeString();
            $to = Carbon::parse($endDate)->addHours(23)->addMinutes(59)->addSeconds(59)->toDateTimeString();
        }

        $model = DB::table('finish_transactions')->selectRaw('finish_transactions.*, finish_transaction_tests.draw_time, finish_transaction_tests.draw_by_name');
        $model->leftJoin('finish_transaction_tests', 'finish_transactions.id', '=', 'finish_transaction_tests.finish_transaction_id');
        $model->where('finish_transactions.created_time', '>=', $from);
        $model->where('finish_transactions.created_time', '<=', $to);
        if ($specimen_id != null && $specimen_id != "null" && $specimen_id != 0) {
            $model->where('finish_transaction_tests.group_id', '=', $specimen_id);
        }
        $model->orderBy('finish_transactions.created_time', 'desc');
        $model->groupBy('finish_transactions.id');
        // $model->groupBy('finish_transaction_tests.specimen_id');

        return DataTables::of($model)
            ->addIndexColumn()
            ->escapeColumns([])
            ->make(true);
    }

    public function flebotomiSamplingPrint($startDate = null, $endDate = null, $specimen_id = null)
    {
        // if the startDate and the endDate not set, the query will be only for today's transactions
        if ($startDate == null && $endDate == null) {
            $from = Carbon::today()->addHours(0)->addMinutes(0)->addSeconds(0)->toDateTimeString();
            $to = Carbon::today()->addHours(23)->addMinutes(59)->addSeconds(59)->toDateTimeString();
        } else {
            $from = Carbon::parse($startDate)->addHours(0)->addMinutes(0)->addSeconds(0)->toDateTimeString();
            $to = Carbon::parse($endDate)->addHours(23)->addMinutes(59)->addSeconds(59)->toDateTimeString();
        }

        $query = DB::table('finish_transactions')->selectRaw('finish_transactions.*, finish_transaction_tests.draw_time, finish_transaction_tests.draw_by_name');
        $query->leftJoin('finish_transaction_tests', 'finish_transactions.id', '=', 'finish_transaction_tests.finish_transaction_id');
        $query->where('finish_transactions.created_time', '>=', $from);
        $query->where('finish_transactions.created_time', '<=', $to);
        if ($specimen_id != null && $specimen_id != "null" && $specimen_id != 0) {
            $query->where('finish_transaction_tests.group_id', '=', $specimen_id);
        }
        $query->orderBy('finish_transactions.created_time', 'desc');
        $query->groupBy('finish_transactions.id');

        $data["samplingData"] = $query->get();

        if ($specimen_id != null && $specimen_id != "null" && $specimen_id != 0) {
            $query_specimen = DB::table('specimens')->select('name as specimen_name');
            $specimen = $query_specimen->first();
            $data["specimen"] = $specimen->specimen_name;
        } else {
            $data["specimen"] = '-';
        }

        if ($startDate != null && $endDate != null) {
            $data["startDate"] = date('d/m/Y', strtotime($startDate));
            $data["endDate"] = date('d/m/Y', strtotime($endDate));
        } else {
            $data["startDate"] = '-';
            $data["endDate"] = '-';
        }

        return view('dashboard.report.report_flebotomi_sampling.print', $data);
    }

    //===============================================================================================
    // VERIFICATION & VALIDATION REPORT
    //===============================================================================================
    public function verificationValidationReport()
    {
        $data['title'] = 'Verification & Validation Report';
        return view('dashboard.report.report_verification_validation.index', $data);
    }

    public function verificationValidationDatatable($startDate = null, $endDate = null, $test_id = null)
    {
        if ($startDate == null && $endDate == null) {
            // $from = Carbon::today()->addHours(0)->addMinutes(0)->addSeconds(0)->toDateTimeString();
            // $to = Carbon::today()->addHours(23)->addMinutes(59)->addSeconds(59)->toDateTimeString();
            $from = date('Y-m-d');
            $to = date('Y-m-d');
        } else {
            $from = Carbon::parse($startDate)->addHours(0)->addMinutes(0)->addSeconds(0)->toDateTimeString();
            $to = Carbon::parse($endDate)->addHours(23)->addMinutes(59)->addSeconds(59)->toDateTimeString();
        }

        $query = "SELECT users.id, users.name, 
            (SELECT COUNT(validate_by)
            FROM finish_transaction_tests tt1
            WHERE tt1.validate_by = users.id AND DATE(tt1.input_time) BETWEEN '" . $from . "' AND  '" . $to . "') AS validator,
            (SELECT COUNT(verify_by)
            FROM finish_transaction_tests tt2
            WHERE tt2.verify_by = users.id AND DATE(tt2.input_time) BETWEEN '" . $from . "' AND  '" . $to . "') AS verifikator
        FROM users
        ORDER BY validator DESC , verifikator desc";

        $model = DB::select(DB::raw($query));


        return DataTables::of($model)
            ->addIndexColumn()
            ->escapeColumns([])
            ->make(true);

        // query 1
        // $user_query = DB::table('users')->select('users.id as user_id', 'users.name as user_name')->orderBy('name');
        // $user_data = $user_query->get();

        // foreach ($user_data as $key => $value) {
        //     echo 'Id : ' . $value->user_id . '<br>';
        //     echo 'User : ' . $value->user_name . '<br>';
        // }

        // return response()->json(['user' => $user_data, 'verification' => $verification_data, 'validation' => $validation_data]);

        // query 2
        // $query = DB::table('finish_transaction_tests');
        // $query->select('users.name as analyst_name', DB::raw('COUNT(finish_transaction_tests.verify_by) as jumlah_verifikasi'), DB::raw('COUNT(finish_transaction_tests.validate_by) as jumlah_validasi'));
        // $query->leftJoin('users', 'finish_transaction_tests.verify_by', '=', 'users.id');
        // if ($test_id != null) {
        //     $query->where('test_id', $test_id);
        // }
        // $query->groupBy('users.id');
        // $data = $query->get();

        // return response()->json($data);
    }

    public function verificationValidationPrint($startDate = null, $endDate = null, $test_id = null)
    {
        // if the startDate and the endDate not set, the query will be only for today's transactions
        if ($startDate == null && $endDate == null) {
            $from = Carbon::today()->addHours(0)->addMinutes(0)->addSeconds(0)->toDateTimeString();
            $to = Carbon::today()->addHours(23)->addMinutes(59)->addSeconds(59)->toDateTimeString();
        } else {
            $from = Carbon::parse($startDate)->addHours(0)->addMinutes(0)->addSeconds(0)->toDateTimeString();
            $to = Carbon::parse($endDate)->addHours(23)->addMinutes(59)->addSeconds(59)->toDateTimeString();
        }

        $query = DB::table('finish_transaction_tests')->select('finish_transaction_tests.id', 'users.name as user_name', DB::raw('COUNT(finish_transaction_tests.verify_by) as jumlah_verifikasi'), DB::raw('COUNT(finish_transaction_tests.validate_by) as jumlah_validasi'));
        $query->leftJoin('users', 'finish_transaction_tests.verify_by', '=', 'users.id');
        $query->where('finish_transaction_tests.input_time', '>=', $from);
        $query->where('finish_transaction_tests.input_time', '<=', $to);
        if ($test_id != null && $test_id != "null" && $test_id != 0) {
            $query->where('test_id', $test_id);
        }
        $query->orderBy('users.name', 'asc');
        $query->groupBy('users.id');

        $data['verf_val_data'] = $query->get();

        if ($test_id != null && $test_id != "null" && $test_id != 0) {
            $query_test = DB::table('finish_transaction_tests')->select('finish_transaction_tests.test_name')->where('test_id', $test_id);
            $test = $query_test->first();
            $data["test_name"] = $test->test_name;
        } else {
            $data["test_name"] = '-';
        }

        if ($startDate != null && $endDate != null) {
            $data["startDate"] = date('d/m/Y', strtotime($startDate));
            $data["endDate"] = date('d/m/Y', strtotime($endDate));
        } else {
            $data["startDate"] = '-';
            $data["endDate"] = '-';
        }

        return view('dashboard.report.report_verification_validation.print', $data);
    }

    //===============================================================================================
    // TB04 REPORT
    //===============================================================================================
    public function TB04Report()
    {
        $data['title'] = 'Verification & Validation Report';
        return view('dashboard.report.report_tb04.index', $data);
    }

    public function TB04Datatable($startDate = null, $endDate = null, $group_id = null)
    {
        if ($startDate == null && $endDate == null) {
            $from = Carbon::today()->addHours(0)->addMinutes(0)->addSeconds(0)->toDateTimeString();
            $to = Carbon::today()->addHours(23)->addMinutes(59)->addSeconds(59)->toDateTimeString();
        } else {
            $from = Carbon::parse($startDate)->addHours(0)->addMinutes(0)->addSeconds(0)->toDateTimeString();
            $to = Carbon::parse($endDate)->addHours(23)->addMinutes(59)->addSeconds(59)->toDateTimeString();
        }

        $model = DB::table('finish_transactions')->selectRaw('finish_transactions.*');
        $model->where('created_time', '>=', $from);
        $model->where('created_time', '<=', $to);
        $model->orderBy('created_time', 'desc');

        return DataTables::of($model)
            ->addIndexColumn()
            ->escapeColumns([])
            ->make(true);
    }

    public function TB04Print($startDate = null, $endDate = null)
    {
        // if the startDate and the endDate not set, the query will be only for today's transactions
        if ($startDate == null && $endDate == null) {
            $from = Carbon::today()->addHours(0)->addMinutes(0)->addSeconds(0)->toDateTimeString();
            $to = Carbon::today()->addHours(23)->addMinutes(59)->addSeconds(59)->toDateTimeString();
        } else {
            $from = Carbon::parse($startDate)->addHours(0)->addMinutes(0)->addSeconds(0)->toDateTimeString();
            $to = Carbon::parse($endDate)->addHours(23)->addMinutes(59)->addSeconds(59)->toDateTimeString();
        }

        $query = DB::table('finish_transactions')->select('finish_transactions.*')
            ->where('created_time', '>=', $from)
            ->where('created_time', '<=', $to)
            ->orderBy('insurance_id', 'asc')
            ->groupBy('insurance_id');

        $data['tb04Data'] = $query->get();

        if ($startDate != null && $endDate != null) {
            $data["startDate"] = date('d/m/Y', strtotime($startDate));
            $data["endDate"] = date('d/m/Y', strtotime($endDate));
        } else {
            $data["startDate"] = '-';
            $data["endDate"] = '-';
        }

        return view('dashboard.report.report_tb04.print', $data);
    }

    //===============================================================================================
    // INSURANCE REPORT
    //===============================================================================================
    public function insuranceReport()
    {
        $data['title'] = 'Insurance Report';
        return view('dashboard.report.report_insurance.index', $data);
    }

    public function insuranceDatatable($startDate = null, $endDate = null)
    {
        if ($startDate == null && $endDate == null) {
            $from = Carbon::today()->addHours(0)->addMinutes(0)->addSeconds(0)->toDateTimeString();
            $to = Carbon::today()->addHours(23)->addMinutes(59)->addSeconds(59)->toDateTimeString();
        } else {
            $from = Carbon::parse($startDate)->addHours(0)->addMinutes(0)->addSeconds(0)->toDateTimeString();
            $to = Carbon::parse($endDate)->addHours(23)->addMinutes(59)->addSeconds(59)->toDateTimeString();
        }

        $model = DB::table('finish_transactions')->select('id', 'created_time', 'insurance_name', DB::raw('COUNT(insurance_id) as total_pasien'))
            ->where('created_time', '>=', $from)
            ->where('created_time', '<=', $to)
            ->orderBy('insurance_id', 'asc')
            ->groupBy('insurance_id');

        return DataTables::of($model)
            ->addIndexColumn()
            ->escapeColumns([])
            ->make(true);
    }

    public function insurancePrint($startDate = null, $endDate = null)
    {
        // if the startDate and the endDate not set, the query will be only for today's transactions
        if ($startDate == null && $endDate == null) {
            $from = Carbon::today()->addHours(0)->addMinutes(0)->addSeconds(0)->toDateTimeString();
            $to = Carbon::today()->addHours(23)->addMinutes(59)->addSeconds(59)->toDateTimeString();
        } else {
            $from = Carbon::parse($startDate)->addHours(0)->addMinutes(0)->addSeconds(0)->toDateTimeString();
            $to = Carbon::parse($endDate)->addHours(23)->addMinutes(59)->addSeconds(59)->toDateTimeString();
        }

        $query = DB::table('finish_transactions')->select('id', 'created_time', 'insurance_name', DB::raw('COUNT(insurance_id) as total_pasien'))
            ->where('created_time', '>=', $from)
            ->where('created_time', '<=', $to)
            ->orderBy('insurance_id', 'asc')
            ->groupBy('insurance_id');

        $data['insuranceData'] = $query->get();

        if ($startDate != null && $endDate != null) {
            $data["startDate"] = date('d/m/Y', strtotime($startDate));
            $data["endDate"] = date('d/m/Y', strtotime($endDate));
        } else {
            $data["startDate"] = '-';
            $data["endDate"] = '-';
        }

        return view('dashboard.report.report_insurance.print', $data);
    }

    //===============================================================================================
    // BILLING REPORT
    //===============================================================================================
    public function billingReport()
    {
        $data['title'] = 'Billing Report';
        return view('dashboard.report.report_billing.index', $data);
    }

    public function billingDatatable($startDate = null, $endDate = null, $insurance_id = null)
    {
        if ($startDate == null && $endDate == null) {
            $from = Carbon::today()->addHours(0)->addMinutes(0)->addSeconds(0)->toDateTimeString();
            $to = Carbon::today()->addHours(23)->addMinutes(59)->addSeconds(59)->toDateTimeString();
        } else {
            $from = Carbon::parse($startDate)->addHours(0)->addMinutes(0)->addSeconds(0)->toDateTimeString();
            $to = Carbon::parse($endDate)->addHours(23)->addMinutes(59)->addSeconds(59)->toDateTimeString();
        }

        $queryPackagePrice = "(select price from prices where package_id = finish_transaction_tests.package_id LIMIT 1) as package_price";
        $queryTestPrice = "(select price from prices where test_id = finish_transaction_tests.test_id LIMIT 1) as test_price";

        $model = DB::table('finish_transactions')->selectRaw("finish_transactions.*, finish_transaction_tests.test_name, finish_transaction_tests.package_id, finish_transaction_tests.package_name, prices.price, $queryPackagePrice, $queryTestPrice");
        $model->leftJoin('finish_transaction_tests', 'finish_transactions.id', '=', 'finish_transaction_tests.finish_transaction_id');
        $model->leftJoin('prices', 'finish_transaction_tests.price_id', '=', 'prices.id');
        $model->where('finish_transactions.created_time', '>=', $from);
        $model->where('finish_transactions.created_time', '<=', $to);
        if ($insurance_id != null && $insurance_id != "null" && $insurance_id != 0) {
            $model->where('finish_transactions.insurance_id', '=', $insurance_id);
        }
        $model->orderBy('finish_transactions.created_time', 'desc');
        // $query->groupBy('finish_transaction_tests.package_id');

        return DataTables::of($model)
            ->addIndexColumn()
            ->escapeColumns([])
            ->make(true);
    }

    public function billingPrint($startDate = null, $endDate = null, $insurance_id = null)
    {
        if ($startDate == null && $endDate == null) {
            $from = Carbon::today()->addHours(0)->addMinutes(0)->addSeconds(0)->toDateTimeString();
            $to = Carbon::today()->addHours(23)->addMinutes(59)->addSeconds(59)->toDateTimeString();
        } else {
            $from = Carbon::parse($startDate)->addHours(0)->addMinutes(0)->addSeconds(0)->toDateTimeString();
            $to = Carbon::parse($endDate)->addHours(23)->addMinutes(59)->addSeconds(59)->toDateTimeString();
        }

        $queryPackagePrice = "(select price from prices where package_id = finish_transaction_tests.package_id LIMIT 1) as package_price";
        $queryTestPrice = "(select price from prices where test_id = finish_transaction_tests.test_id LIMIT 1) as test_price";

        $query = DB::table('finish_transactions')->selectRaw("finish_transactions.*, finish_transaction_tests.test_name, finish_transaction_tests.package_id, finish_transaction_tests.package_name, prices.price, $queryPackagePrice, $queryTestPrice");
        $query->leftJoin('finish_transaction_tests', 'finish_transactions.id', '=', 'finish_transaction_tests.finish_transaction_id');
        $query->leftJoin('prices', 'finish_transaction_tests.price_id', '=', 'prices.id');
        $query->where('finish_transactions.created_time', '>=', $from);
        $query->where('finish_transactions.created_time', '<=', $to);
        if ($insurance_id != null && $insurance_id != "null" && $insurance_id != 0) {
            $query->where('finish_transactions.insurance_id', '=', $insurance_id);
        }
        $query->orderBy('finish_transactions.created_time', 'desc');
        // $query->groupBy('finish_transaction_tests.package_id');

        $data["insuranceData"] = $query->get();

        if ($insurance_id != null && $insurance_id != "null" && $insurance_id != 0) {
            $query_insurance = DB::table('insurances')->select('name as insurance_name');
            $insurance = $query_insurance->first();
            $data["insurance"] = $insurance->insurance_name;
        } else {
            $data["insurance"] = '-';
        }

        if ($startDate != null && $endDate != null) {
            $data["startDate"] = date('d/m/Y', strtotime($startDate));
            $data["endDate"] = date('d/m/Y', strtotime($endDate));
        } else {
            $data["startDate"] = '-';
            $data["endDate"] = '-';
        }

        return view('dashboard.report.report_billing.print', $data);
    }

    //===============================================================================================
    // DOCTOR REPORT
    //===============================================================================================
    public function doctorReport()
    {
        $data['title'] = 'Doctor Report';
        return view('dashboard.report.report_doctor.index', $data);
    }

    public function doctorDatatable($startDate = null, $endDate = null, $doctor_id = null)
    {
        // if the startDate and the endDate not set, the query will be only for today's transactions
        if ($startDate == null && $endDate == null) {
            $from = Carbon::today()->addHours(0)->addMinutes(0)->addSeconds(0)->toDateTimeString();
            $to = Carbon::today()->addHours(23)->addMinutes(59)->addSeconds(59)->toDateTimeString();
        } else {
            $from = Carbon::parse($startDate)->addHours(0)->addMinutes(0)->addSeconds(0)->toDateTimeString();
            $to = Carbon::parse($endDate)->addHours(23)->addMinutes(59)->addSeconds(59)->toDateTimeString();
        }

        $model = \App\FinishTransaction::selectRaw('COUNT(finish_transactions.patient_id) as total_patient, finish_transactions.id, finish_transactions.doctor_name, finish_transactions.created_time')
            ->where('created_time', '>=', $from)
            ->where('created_time', '<=', $to);
        if ($doctor_id != null && $doctor_id != "null" && $doctor_id != 0) {
            $model->where('finish_transactions.doctor_id', '=', $doctor_id);
        }
        $model->groupBy('doctor_id');

        return DataTables::of($model)
            ->addIndexColumn()
            ->escapeColumns([])
            ->make(true);
    }

    public function doctorPrint($startDate = null, $endDate = null, $doctor_id = null)
    {
        // if the startDate and the endDate not set, the query will be only for today's transactions
        if ($startDate == null && $endDate == null) {
            $from = Carbon::today()->addHours(0)->addMinutes(0)->addSeconds(0)->toDateTimeString();
            $to = Carbon::today()->addHours(23)->addMinutes(59)->addSeconds(59)->toDateTimeString();
        } else {
            $from = Carbon::parse($startDate)->addHours(0)->addMinutes(0)->addSeconds(0)->toDateTimeString();
            $to = Carbon::parse($endDate)->addHours(23)->addMinutes(59)->addSeconds(59)->toDateTimeString();
        }

        $query = \App\FinishTransaction::selectRaw('COUNT(finish_transactions.patient_id) as total_patient, finish_transactions.id, finish_transactions.doctor_name, finish_transactions.created_time')
            ->where('created_time', '>=', $from)
            ->where('created_time', '<=', $to);
        if ($doctor_id != null && $doctor_id != "null" && $doctor_id != 0) {
            $query->where('finish_transactions.doctor_id', '=', $doctor_id);
        }
        $query->groupBy('doctor_id');

        $data["doctorData"] = $query->get();

        if ($doctor_id != null && $doctor_id != "null" && $doctor_id != 0) {
            $query_doctor = DB::table('doctors')->select('doctors.*')->where('id', $doctor_id);
            $doctor = $query_doctor->first();
            $data["doctor"] = $doctor->name;
        } else {
            $data["doctor"] = '-';
        }

        if ($startDate != null && $endDate != null) {
            $data["startDate"] = date('d/m/Y', strtotime($startDate));
            $data["endDate"] = date('d/m/Y', strtotime($endDate));
        } else {
            $data["startDate"] = '-';
            $data["endDate"] = '-';
        }

        return view('dashboard.report.report_doctor.print', $data);
    }
}
