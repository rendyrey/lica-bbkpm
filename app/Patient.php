<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class Patient extends Model
{
    protected $fillable = [
        'medrec',
        'nik',
        'name',
        'gender',
        'birthdate',
        'address',
        'phone',
        'email'
    ];


    public static function validate($request)
    {
        return Validator::make(
            $request->all(),
            [
                'nik' => 'required',
                'name' => 'required',
                'gender' => 'required',
                'birthdate' => 'required',
                'address' => 'required',
                'phone' => 'required',
                'email' => 'required'
            ]
        );
    }
}
