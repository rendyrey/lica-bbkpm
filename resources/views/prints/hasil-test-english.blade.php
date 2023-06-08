<!DOCTYPE html>
<html>

<head>
    <!-- <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/> -->
    <title></title>
    <style type="text/css">
        body {
            font-family: "Arial", sans-serif;
            /*font-family: 'Courier New', monospace;*/
            font-size: 12px;
            /*margin-left: 2em; */
            /*margin-top: -5em;*/
        }

        #header {
            /* position: fixed;
            /* width: 100%;
            height: 100%; */
            /* padding: 10px; */
            margin-top: 0px
        }

        p {
            margin: 0;
            /*display: table-cell;*/
        }

        /* #content {
            margin-top: 150px;
            display: inline;
        }
        .spacer
        {
            width: 100%;
            height: 95px;
        } */
        table {
            width: 100%;
            margin: 0 auto;
        }

        tr th {
            background: #eee;

        }

        .border-bottom {
            border-bottom: 1px solid black;
        }

        tr,
        td {
            /* border: 1px solid black; */
        }

        @media print {
            .page-break {
                page-break-before: always;
            }
        }

        caption {
            text-align: left;
        }

        img {
            margin-top: 10px;
            /* margin-left: 40px;
            height: 100px;
            width: 100px; */
        }

        .content-width {
            padding-right: 45px;
        }

        .footer-width {
            padding-right: 100px;
        }

        .footer-bottom {
            padding-bottom: 125px;
        }

        #header {
            /*border-style: solid;
            border-width: 1px;*/
            margin-bottom: 10px;
            margin-top: -15px
        }
    </style>
</head>

<body>
    <div>
        <div id="header">
            <div>
                <img src="{{asset('images/kop-surat-3.jpg')}}" style="height: 100%; width: 100%">
            </div>
        </div>
        <table>
            <tr>
                <td style="text-align: left;"><b>CLINICAL LABORATORY</b></td>
                <td style="text-align: right;">Print : {{$transaction->print}}</td>
            </tr>
        </table>
        <table>
            <tbody>
                <tr>
                    <td style="text-align: left;" width="17%"><b>Doctor In Charge</b></td>
                    <td style="text-align: left;" width="83%">: dr. Emmanuel, Sp. PK</td>
                </tr>
            </tbody>
        </table>
        <!-- <hr style="border: 1px solid black"> -->
        <br>
        <table>
            <tbody>
                <tr>
                    <td style="text-align: left; width: 17%;">Patient Name</td>
                    <td style="text-align: left; width: 1%;">:</td>
                    <td style="text-align: left; width: 39%;"><b> {{ $transaction->patient_name }} </b> </td>
                    <td style="text-align: left; width: 15%;">Date </b></td>
                    <td style="text-align: left; width: 1%;">:</td>
                    <td style="text-align: left; width: 27%;"><?= date('d/m/Y', strtotime($transaction->created_time)); ?></td>

                </tr>
                <tr>
                    <td style="text-align: left;">NIK</td>
                    <td style="text-align: left;">:</td>
                    <td style="text-align: left;">{{ $transaction->patient_nik }} </td>
                    <td style="text-align: left;">Diagnosis </td>
                    <td style="text-align: left;">:</td>
                    <td style="text-align: left;">{{ $transaction->note }}</td>
                </tr>
                <tr>
                    <td style="text-align: left;">Birthdate / Age</td>
                    <td style="text-align: left;">:</td>
                    <td style="text-align: left;"><?= date('d/m/Y',  strtotime($transaction->patient_birthdate)); ?> / {{$age}}</b></td>
                    <td style="text-align: left;">Physician </td>
                    <td style="text-align: left;">:</td>
                    <td style="text-align: left;">{{ $transaction->doctor_name }}</td>
                </tr>
                <tr>
                    <td style="text-align: left;">Medrec / No Lab</td>
                    <td style="text-align: left;">:</td>
                    <td style="text-align: left;"><b>{{$transaction->patient_medrec}} / {{$transaction->no_lab}}</td>
                    <td style="text-align: left;">Clinic </td>
                    <td style="text-align: left;">:</td>
                    <td style="text-align: left;">{{ $transaction->room_name }}</td>
                </tr>
                <tr>
                    <td style="text-align: left;">Gender </td>
                    <td style="text-align: left;">:</td>
                    @if ($transaction->patient_gender == 'M')
                    <td style="text-align: left;">Male</td>
                    @elseif ($transaction->patient_gender == 'F')
                    <td style="text-align: left;">Female</td>
                    @endif

                    <td style="text-align: left;">Patient Type </td>
                    <td style="text-align: left;">:</td>
                    <td style="text-align: left;">{{ $transaction->insurance_name }} </td>

                </tr>
                <tr>
                    <td style="text-align: left">Address </td>
                    <td style="text-align: left">:</td>
                    <td style="text-align: left">{{ $transaction->patient_address }} </td>
                </tr>
            </tbody>
        </table>
        <hr style="border: 1px solid black">
        <table id="tb_result" width="100%" style="border-bottom: 1px solid black">
            <thead>
                <tr>
                    <th class="border-bottom" id="content" style="text-align: left; padding-left:15px; width:25%;">EXAMINATION TYPE</th>
                    <th class="border-bottom" id="content" style="width:5%;"></th>
                    <th class="border-bottom" id="content" style="text-align: left; width:15%;">RESULT</th>
                    <th class="border-bottom" id="content" style="text-align: left; width:20%;">REFERENCE VALUE</th>
                    <th class="border-bottom" id="content" style="text-align: left; width:10%;">UNIT</th>
                    <th class="border-bottom" id="content" style="text-align: left; width:20%;">NOTE</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $group_test = '';
                $sub_group = '';
                $package_name = '';
                $new_page = 0;
                $row = 0;
                $page = 1; ?>
                @foreach ($tests as $test)
                <?php $hitung;
                ?>
                @if((($group_test != '') && ($group_test != $test->group_name) && (count($groups[$test->group_name]) + $row > 25)) || ($row > 50))
                <?php $row = 0; ?>
            </tbody>
        </table>
        <hr>
        <table>
            <tbody>
                <tr>
                    <!--<td style="text-align: right;"> Hal.  {{ $page }}</td>-->
                </tr>
            </tbody>
        </table>
        <?php $page++; ?>
        <div id="header" class="page-break">
            <div>
                <img src="{{asset('images/kop-surat-3.jpg')}}" style="height: 100%; width: 100%">
            </div>
        </div>
        <table>
            <tr>
                <td style="text-align: left;"><b>CLINICAL LABORATORY</b></td>
                <td style="text-align: right;">Print : {{$transaction->print}}</td>
            </tr>
        </table>
        <table>
            <tbody>
                <tr>
                    <td style="text-align: left;" width="17%"><b>Doctor In Charge</b></td>
                    <td style="text-align: left;" width="83%">: dr. Emmanuel, Sp. PK</td>
                </tr>
            </tbody>
        </table>
        <!-- <hr style="border: 1px solid black"> -->
        <br>
        <table>
            <tbody>
                <tr>
                    <td style="text-align: left; width: 17%;">Patient Name</td>
                    <td style="text-align: left; width: 1%;">:</td>
                    <td style="text-align: left; width: 39%;"><b> {{ $transaction->patient_name }} </b> </td>
                    <td style="text-align: left; width: 15%;">Date </b></td>
                    <td style="text-align: left; width: 1%;">:</td>
                    <td style="text-align: left; width: 27%;"><?= date('d/m/Y', strtotime($transaction->created_time)); ?></td>

                </tr>
                <tr>
                    <td style="text-align: left;">NIK</td>
                    <td style="text-align: left;">:</td>
                    <td style="text-align: left;">{{ $transaction->patient_nik }} </td>
                    <td style="text-align: left;">Diagnosis </td>
                    <td style="text-align: left;">:</td>
                    <td style="text-align: left;">{{ $transaction->note }}</td>
                </tr>
                <tr>
                    <td style="text-align: left;">Birthdate / Age</td>
                    <td style="text-align: left;">:</td>
                    <td style="text-align: left;"><?= date('d/m/Y',  strtotime($transaction->patient_birthdate)); ?> / {{$age}}</b></td>
                    <td style="text-align: left;">Physician </td>
                    <td style="text-align: left;">:</td>
                    <td style="text-align: left;">{{ $transaction->doctor_name }}</td>
                </tr>
                <tr>
                    <td style="text-align: left;">Medrec / No Lab</td>
                    <td style="text-align: left;">:</td>
                    <td style="text-align: left;"><b>{{$transaction->patient_medrec}} / {{$transaction->no_lab}}</td>
                    <td style="text-align: left;">Clinic </td>
                    <td style="text-align: left;">:</td>
                    <td style="text-align: left;">{{ $transaction->room_name }}</td>
                </tr>
                <tr>
                    <td style="text-align: left;">Gender </td>
                    <td style="text-align: left;">:</td>
                    @if ($transaction->patient_gender == 'M')
                    <td style="text-align: left;">Male</td>
                    @elseif ($transaction->patient_gender == 'F')
                    <td style="text-align: left;">Female</td>
                    @endif

                    <td style="text-align: left;">Patient Type </td>
                    <td style="text-align: left;">:</td>
                    <td style="text-align: left;">{{ $transaction->insurance_name }} </td>

                </tr>
                <tr>
                    <td style="text-align: left">Address </td>
                    <td style="text-align: left">:</td>
                    <td style="text-align: left">{{ $transaction->patient_address }} </td>
                </tr>
            </tbody>
        </table>
        <hr style="border: 1px solid black">
        <table id="tb_result" width="100%" style="border-bottom: 1px solid black;">
            <tr>
                <th class="border-bottom" id="content" style="text-align: left; padding-left:15px; width:25%;">EXAMINATION TYPE</th>
                <th class="border-bottom" id="content" style="width:5%;"></th>
                <th class="border-bottom" id="content" style="text-align: left; width:15%;">RESULT</th>
                <th class="border-bottom" id="content" style="text-align: left; width:20%;">REFERENCE VALUE</th>
                <th class="border-bottom" id="content" style="text-align: left; width:10%;">UNIT</th>
                <th class="border-bottom" id="content" style="text-align: left; width:20%;">NOTE</th>
            </tr>
            </thead>
            <?php $new_page = 1; ?>
            @endif
            @if (($group_test == '')||($group_test != $test->group_name))
            <?php $group_test = $test->group_name; ?>

            <tr id="content">
                <td style="padding-left:15px; padding-bottom: 5px;"><span style="font-size:10px; font-style: italic; font-weight: bold">{{ $test->group_name }}</span></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <?php $row++; ?>
            @endif

            @if($test->print_package_name == 1)
            @if (($package_name == '')||($package_name != $test->package_name))
            <?php $package_name = $test->package_name; ?>

            <tr id="content">
                <td style="padding-left:15px; padding-bottom: 5px;"><span style="font-size:10px; font-weight: bold">{{ $test->package_name }}</span></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <?php $row++; ?>

            @endif
            @endif

            @if (($sub_group == '')||($sub_group != $test->sub_group))
            @if ($test->sub_group != '')
            <tr id="content">
                <td style="padding-left:25px; padding-bottom: 3px;"><span style="font-size:10px;font-weight: bold">{{ $test->sub_group }}</span></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <?php $row++; ?>

            @endif
            <?php $sub_group = $test->sub_group; ?>

            @endif
            <tr id="content">

                <!-- td test name -->
                @if ($sub_group == '')
                <td id="content" style="padding-left:35px; padding-bottom: 1px;">{{ $test->test_name }}</td>
                @else
                <td id="content" style="padding-left:35px; padding-bottom: 1px;">{{ $test->test_name }}</td>
                @endif
                <!-- end td test name -->

                <!-- td flagging  -->
                @if ($test->result_status_label == 'Critical')
                <td id="content" style="text-align: right; padding-bottom: 3px; color: red">**</td>
                @elseif ($test->result_status_label == 'Abnormal')
                <td id="content" style="text-align: right; padding-bottom: 3px; color: red">*</td>
                @elseif ($test->result_status_label == 'Normal')
                <td id="content" style="text-align: right; padding-bottom: 3px;"></td>
                @else
                <td id="content" style="text-align: right; padding-bottom: 3px;"></td>
                @endif
                <!-- end td flagging -->

                <?php
                $result = $test->global_result;

                if (is_string($test->global_result)) {

                    // $result = str_replace(" ; ","<br>",$test->global_result ); 
                    $result = $test->global_result;
                }
                ?>

                <!-- td result -->
                @if (strlen($test->global_result) > 15)

                @if ($test->result_status_label == 'Critical')
                <td id="content" style="text-align: left; padding-bottom: 3px; color: red">{!! $result !!}</td>
                @elseif ($test->result_status_label == 'Abnormal')
                <td id="content" style="text-align: left; padding-bottom: 3px; color: red">{!! $result !!}</td>
                @elseif ($test->result_status_label == 'Normal')

                <!-- if global result string is date format, then convert into date format -->
                @if (DateTime::createFromFormat('Y-m-d', $result) !== false)
                @php
                $result_date = date('d/m/Y', strtotime($result)); 
                @endphp
                <td id="content" style="text-align: left; padding-bottom: 3px;"> {!! $result_date !!}</td>
                @else
                
                @if ($test->normal_value == '' && $test->test_unit == '')
                <td colspan="4" id="content" style="text-align: left; padding-bottom: 3px;">{!! $result !!}</td>
                @else
                <td id="content" style="text-align: right; padding-bottom: 3px;">{!! $result !!}</td>
                @endif

                @endif

                @else
                
                <!-- if global result string is date format, then convert into date format -->
                @if (DateTime::createFromFormat('Y-m-d', $result) !== false)
                @php
                $result_date = date('d/m/Y', strtotime($result)); 
                @endphp
                <td id="content" style="text-align: left; padding-bottom: 3px;"> {!! $result_date !!}</td>
                @else
                
                @if ($test->normal_value == '' && $test->unit == '')
                <td colspan="4" id="content" style="text-align: left; padding-bottom: 3px;">{!! $result !!}</td>
                @else
                <td id="content" style="text-align: right; padding-bottom: 3px;">{!! $result !!}</td>
                @endif

                @endif

                @endif

                <?php $row = $row++; ?>
                @else

                @if ($test->result_status_label == 'Critical')
                <td id="content" style="text-align: left; padding-bottom: 3px; color: red">{!! $result !!}</td>
                @elseif ($test->result_status_label == 'Abnormal')
                <td id="content" style="text-align: left; padding-bottom: 3px; color: red">{!! $result !!}</td>
                @elseif ($test->result_status_label == 'Normal')
                <td id="content" style="text-align: left; padding-bottom: 3px;">
                    <!-- if global result string is date format, then convert into date format -->
                    @if (DateTime::createFromFormat('Y-m-d', $result) !== false)

                    @php
                    $result_date = date('d/m/Y', strtotime($result));
                    @endphp

                    {!! $result_date !!}

                    @else
                    {!! $result !!}
                    @endif
                </td>
                @else
                <td id="content" style="text-align: left; padding-bottom: 3px;">
                    <!-- if global result string is date format, then convert into date format -->
                    @if (DateTime::createFromFormat('Y-m-d', $result) !== false)

                    @php
                    $result_date = date('d/m/Y', strtotime($result));
                    @endphp

                    {!! $result_date !!}

                    @else
                    {!! $result !!}
                    @endif
                </td>
                @endif

                @endif
                <!-- end td result -->

                <td id="content" style="text-align: left; padding-bottom: 3px;">{!! $test->normal_value !!}</td>
                <td id="content" style="text-align: left; padding-bottom: 3px;">{{ $test->unit }}</td>

                <td id="content" style="text-align: center;">{{ $test->memo_test }}</td>

            </tr>
            <?php $row++; ?>
            @endforeach
            </tbody>
        </table>

        <!-- End Result Table -->

        <br>
        <table>
            <tbody>
                @if($transaction->is_print_memo != '')
                <tr>
                    <td style="text-align: left;">Keterangan :</td>
                </tr>
                <tr>
                    <td style="text-align: left;">
                        <?php
                        echo $transaction->memo_result;
                        ?>
                        <br>
                        <br>

                        @foreach ($tests as $test)
                        @if($test->test_id == 363)
                        @if($test->global_result == 'Positif')
                        - Pemeriksaan konfirmasi dengan RT-PCR <br>
                        - Lakukan karantina atau isolasi sesuai dengan kriteria <br>
                        - Menerapkan perilaku hidup bersih dan sehat (Cuci tangan, terapkan etika batuk, gunakan masker, jaga stamina), serta lakukan physical distancing
                        @else
                        - Hasil pemeriksaan diatas hanya menggambarkan kondisi saat pengambilan spesimen <br>
                        - Hasil Negatif tidak menyingkirkan kemungkinan terinfeksi SARS CoV-2 sehingga masih beresiko menularkan ke oranglain <br>
                        - Hasil Negatif dapat terjadi pada kondisi kuantitas antigen pada spesimen di bawah level deteksi alat yang digunakan <br>
                        - Disarankan tes ulang atau tes konfirmasi dengan pemeriksaan molekular (RT-PCR) terutama apabila pasien bergejala atau diketahui memiliki kontak erat dengan orang yang terkonfirmasi COVID-19 <br>
                        @endif
                        @endif
                        @endforeach
                    </td>
                </tr>
                @else

                @foreach ($tests as $test)
                @if($test->test_id == 363)
                <tr>
                    <td style="text-align: left;">
                        @if($test->global_result == 'Positif')
                        - Pemeriksaan konfirmasi dengan RT-PCR <br>
                        - Lakukan karantina atau isolasi sesuai dengan kriteria <br>
                        - Menerapkan perilaku hidup bersih dan sehat (Cuci tangan, terapkan etika batuk, gunakan masker, jaga stamina), serta lakukan physical distancing
                        @else
                        - Hasil pemeriksaan diatas hanya menggambarkan kondisi saat pengambilan spesimen <br>
                        - Hasil Negatif tidak menyingkirkan kemungkinan terinfeksi SARS CoV-2 sehingga masih beresiko menularkan ke oranglain <br>
                        - Hasil Negatif dapat terjadi pada kondisi kuantitas antigen pada spesimen di bawah level deteksi alat yang digunakan <br>
                        - Disarankan tes ulang atau tes konfirmasi dengan pemeriksaan molekular (RT-PCR) terutama apabila pasien bergejala atau diketahui memiliki kontak erat dengan orang yang terkonfirmasi COVID-19 <br>
                        @endif

                    </td>
                </tr>
                @endif
                @endforeach

                @endif
            </tbody>
        </table>
        <br>

        <table>
            <tbody>
                <tr>
                    <td style="text-align: left; width: 19%;"> Sampling Time </td>
                    <td style="text-align: left; width: 20%;"> : <?= date('d/m/Y H:i', strtotime($last_draw_time)); ?></td>
                    <td style="text-align: left; width: 25%;"> </td>
                    <td style="text-align: left; width: 25%;"> </td>
                </tr>
                <tr>
                    <td style="text-align: left; width: 19%;"> Printing Time </td>
                    <td style="text-align: left; width: 20%;"> : <?= date('d/m/Y H:i', strtotime($print_time)); ?></td>
                    <td style="text-align: left; width: 25%;"> </td>
                    <td style="text-align: left; width: 25%;"> </td>
                </tr>
            </tbody>
        </table>

        <table style="position: fixed;">
            <tbody>
                <tr>
                    <td width="80%" style="text-align: right;"> </td>
                    <td width="20%" style="text-align: center;">Bandung, <?= date('d/m/Y', strtotime($print_time)); ?> </td>
                </tr>
                <tr>
                    <td width="80%" style="text-align: right;"> </td>
                    <td width="20%" style="text-align: center;">Pemeriksa,<br><br><br><br></td>
                </tr>
                <tr>
                    <td width="80%" style="text-align: right;"> </td>
                    <td width="20%" style="text-align: center;">{{ $transaction->verficator_name }}</td>
                </tr>

            </tbody>
        </table>

</body>

</html>