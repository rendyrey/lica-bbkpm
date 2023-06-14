<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\PatientsExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Patient;
use DB;

class ExportImportController extends Controller
{
    public function patientsExport()
    {
        return Excel::download(new PatientsExport, 'Patients.xlsx');
    }

    public function patientsImport(Request $request)
    {
        try{
            $data = Excel::toArray('', $request->file('file_excel'))[0];
            DB::beginTransaction();
            foreach(array_slice($data, 1) as $row) {
                if ($row[1] == NULL || $row[1] == '') {
                    continue;
                }

                $patient = Patient::where('medrec', $row[1])->first();
                if ($patient) {
                    $patient->medrec = $row[1];
                    $patient->name = $row[3];
                    $patient->gender = $row[4];
                    $patient->birthdate = $row[5];
                    $patient->address = $row[6];
                    $patient->phone = $row[7];
                    $patient->email = $row[8];
                    $patient->save();
                } else {
                    $data['medrec'] = $row[1];
                    $data['nik'] = $row[2];
                    $data['name'] = $row[3];
                    $data['gender'] = $row[4];
                    $data['birthdate'] = $row[5];
                    $data['address'] = $row[6];
                    $data['phone'] = $row[7];
                    $data['email'] = $row[8];
                    Patient::create($data);
                }
            }

            DB::commit();
            return redirect('master/patient')->with('message','Successfully Import Patient Data')->with('panel','success');
        } catch (\Exception $e){
            dd($e);
            DB::rollback();
            return redirect('master/patient')->with('message', "Error import patient data, please pay attention to the format, you can export first the patient data")->with('panel','danger');
        }
    }
}
