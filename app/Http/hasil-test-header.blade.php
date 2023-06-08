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
            margin-left: 5px;
            height: 100px;
            width: 380px;
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
            <table>
                <tr>
                    <td width="40%">
                        <div>
                            <img src="{{asset('images/header_kop.jpg')}}">
                        </div>
                    </td>
                    <td width="60%" style="margin-top:-15px;">
                        <p style="font-size: 14px; text-align: right;">
                            Jl. Jend. Gatot Subroto No,517 (Papanggungan) <br>
                            Telp. (022) 7322877 (Customer Service), 7321964 (IGD) <br>
                            Fax. (022) 7322468 Bandung 40285 <br>
                        </p>
                    </td>
                </tr>
            </table>
        </div>
        <br>
        <br>
        <table>
            <tr>
                <td width="20%">
                </td>
                <td width="70%">
                    <p style="font-size: 13px;text-align: center; margin-top: -25px;"><b><u>HASIL PEMERIKSAAN LABORATORIUM</u></b></p>
                    </font>
                </td>
                <td width="10%"></td>
            </tr>
        </table>
        <table>
            <tbody>
                <tr>
                    <td style="text-align: left;" width="17%"><b>Penanggungjawab</b></td>
                    <td style="text-align: left;" width="30%">: <b>dr. Any Yuliani, Sp.PK., M.Kes.</b></td>
                    <td style="text-align: right;">Cetakan ke : {{$transaction->print}}</td>
                </tr>
            </tbody>
        </table>
        <hr style="border: 1px solid black">
        <table>
            <tbody>
                <tr>
                    <td style="text-align: left; width: 17%;">Dokter Pengirim</td>
                    <td style="text-align: left; width: 35%;">: {{ $transaction->doctor_name }} </td>
                    <td style="text-align: left; width: 15%;">Tanggal </b></td>
                    <td style="text-align: left; width: 33%;">: <?= date('d/m/Y', strtotime($transaction->created_time)); ?></td>

                </tr>
                <tr>
                    <td style="text-align: left;">Asal / Ruangan</td>
                    <td style="text-align: left;">: {{ $transaction->room_name }} </td>
                    <td style="text-align: left;">No Lab </td>
                    <td style="text-align: left;">: <b> <?= substr($transaction->no_lab, 8); ?> </b> </td>
                </tr>
                <tr>
                    <td style="text-align: left;">Penjamin</td>
                    <td style="text-align: left;">: {{ $transaction->insurance_name }} </td>
                    <td style="text-align: left;">Nama Pasien </td>
                    <td style="text-align: left;">: <b> {{ $transaction->patient_name }} </b> </td>
                </tr>
                <tr>
                    <td style="text-align: left;">Jam Registrasi</td>
                    <td style="text-align: left;">: <?= date('d/m/Y H:i',  strtotime($transaction->created_time)); ?> </td>
                    <td style="text-align: left;">No. Rekam Medik</td>
                    <td style="text-align: left;">: {{ $transaction->patient_medrec }} </td>
                </tr>
                <tr>
                    <td style="text-align: left;">Jam Periksa</td>
                    <td style="text-align: left;">: <?= date('d/m/Y H:i',  strtotime($transaction->checkin_time)); ?> </td>
                    <td style="text-align: left;">Tanggal Lahir / Umur</td>
                    <td style="text-align: left;">: <?= date('d/m/Y',  strtotime($transaction->patient_birthdate)); ?> / {{$age}} </td>
                </tr>
                <tr>
                    <td style="text-align: left;">Jam Cetak Hasil </td>
                    <td style="text-align: left;">: <?= date('d/m/Y H:i',  strtotime($print_time)); ?> </td>

                    <td style="text-align: left;">Jenis Kelamin </td>
                    @if ($transaction->patient_gender == 'M')
                    <td style="text-align: left;">: Laki-laki</td>
                    @elseif ($transaction->patient_gender == 'F')
                    <td style="text-align: left;">: Perempuan</td>
                    @endif
                </tr>
            </tbody>
        </table>
        <hr style="border: 1px solid black">
        <table id="tb_result" width="100%" style="border-bottom: 1px solid black">
            <thead>

                <!-- JIKA BUKAN TEST PCR, MAKA THEAD NORMAL -->
                <?php if ($visibility == 'hidden') { ?>
                    <tr hidden>
                        <th class="border-bottom" id="content" style="text-align: left; padding-left:15px; width:25%;">JENIS PEMERIKSAAN</th>
                        <th class="border-bottom" id="content" style="width:5%;"></th>
                        <th class="border-bottom" id="content" style="text-align: left; width:15%;">HASIL</th>
                        <th class="border-bottom" id="content" style="text-align: left; width:15%;">NILAI RUJUKAN</th>
                        <th class="border-bottom" id="content" style="text-align: left; width:10%;">SATUAN</th>
                        <th class="border-bottom" id="content" style="text-align: left; width:25%;">KETERANGAN</th>
                    </tr>
                <?php } else { ?>
                    <tr>
                        <th class="border-bottom" id="content" style="text-align: left; padding-left:15px; width:25%;">JENIS PEMERIKSAAN</th>
                        <th class="border-bottom" id="content" style="width:5%;"></th>
                        <th class="border-bottom" id="content" style="text-align: left; width:15%;">HASIL</th>
                        <th class="border-bottom" id="content" style="text-align: left; width:15%;">NILAI RUJUKAN</th>
                        <th class="border-bottom" id="content" style="text-align: left; width:10%;">SATUAN</th>
                        <th class="border-bottom" id="content" style="text-align: left; width:25%;">KETERANGAN</th>
                    </tr>
                <?php } ?>
                <!-- END THEAD NORMAL -->

                <?php
                foreach ($tests as $test) {
                    if ($test->test_name == 'PCR SARS COV2' || $test->test_id == '875') {
                ?>
                        <!-- JIKA TEST PCR, MAKA THEAD KHUSUS -->
                        <tr>
                            <th class="border-bottom" id="content" style="text-align: left; padding-left:15px; width:35%;">JENIS PEMERIKSAAN</th>
                            <th class="border-bottom" id="content" style="text-align: left; width:30%;">HASIL</th>
                            <th class="border-bottom" id="content" style="text-align: left; width:35%;">NILAI RUJUKAN</th>
                        </tr>
                        <!-- END THEAD KHUSUS -->
                <?php
                    }
                }
                ?>

            </thead>
            <tbody>
                <?php
                $group_test = '';
                $sub_group = '';
                $new_page = 0;
                $row = 0;
                $page = 1; ?>
                @foreach ($tests as $test)
                <?php $hitung;
                ?>
                @if((($group_test != '') && ($group_test != $test->group_name) && (count($groups[$test->group_name]) + $row > 30)) || ($row > 50))
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
            <table>
                <tr>
                    <td width="40%">
                        <div>
                            <img src="{{asset('images/header_kop.jpg')}}">
                        </div>
                    </td>
                    <td width="60%" style="margin-top:-15px;">
                        <p style="font-size: 14px; text-align: right;">
                            Jl. Jend. Gatot Subroto No,517 (Papanggungan) <br>
                            Telp. (022) 7322877 (Customer Service), 7321964 (IGD) <br>
                            Fax. (022) 7322468 Bandung 40285 <br>
                        </p>
                    </td>
                </tr>
            </table>
        </div>
        <br>
        <br>
        <table>
            <tr>
                <td width="20%">
                </td>
                <td width="70%">
                    <p style="font-size: 13px;text-align: center; margin-top: -25px;"><b><u>HASIL PEMERIKSAAN LABORATORIUM</u></b></p>
                    </font>
                </td>
                <td width="10%"></td>
            </tr>
        </table>
        <hr style="border: 1px solid black">
        <table>
            <tbody>
                <tr>
                    <td style="text-align: left; width: 17%;">Dokter Pengirim</td>
                    <td style="text-align: left; width: 35%;">: {{ $transaction->doctor_name }} </td>
                    <td style="text-align: left; width: 15%;">Tanggal </b></td>
                    <td style="text-align: left; width: 33%;">: <?= date('d/m/Y', strtotime($transaction->created_time)); ?></td>

                </tr>
                <tr>
                    <td style="text-align: left;">Asal / Ruangan</td>
                    <td style="text-align: left;">: {{ $transaction->room_name }} </td>
                    <td style="text-align: left;">No Lab </td>
                    <td style="text-align: left;">: <b> <?= substr($transaction->no_lab, 8); ?> </b> </td>
                </tr>
                <tr>
                    <td style="text-align: left;">Penjamin</td>
                    <td style="text-align: left;">: {{ $transaction->insurance_name }} </td>
                    <td style="text-align: left;">Nama Pasien </td>
                    <td style="text-align: left;">: <b> {{ $transaction->patient_name }} </b> </td>
                </tr>
                <tr>
                    <td style="text-align: left;">Jam Registrasi</td>
                    <td style="text-align: left;">: <?= date('d/m/Y H:i',  strtotime($transaction->created_time)); ?> </td>
                    <td style="text-align: left;">No. Rekam Medik</td>
                    <td style="text-align: left;">: {{ $transaction->patient_medrec }} </td>
                </tr>
                <tr>
                    <td style="text-align: left;">Jam Periksa</td>
                    <td style="text-align: left;">: <?= date('d/m/Y H:i',  strtotime($transaction->checkin_time)); ?> </td>
                    <td style="text-align: left;">Tanggal Lahir / Umur</td>
                    <td style="text-align: left;">: <?= date('d/m/Y',  strtotime($transaction->patient_birthdate)); ?> / {{$age}} </td>
                </tr>
                <tr>
                    <td style="text-align: left;">Jam Cetak Hasil </td>
                    <td style="text-align: left;">: <?= date('d/m/Y H:i',  strtotime($print_time)); ?> </td>

                    <td style="text-align: left;">Jenis Kelamin </td>
                    @if ($transaction->patient_gender == 'M')
                    <td style="text-align: left;">: Laki-laki</td>
                    @elseif ($transaction->patient_gender == 'F')
                    <td style="text-align: left;">: Perempuan</td>
                    @endif
                </tr>
            </tbody>
        </table>
        <hr style="border: 1px solid black">
        <table id="tb_result" width="100%" style="border-bottom: 1px solid black;">

            <!-- JIKA BUKAN TEST PCR, MAKA THEAD NORMAL -->
            <?php if ($visibility == 'hidden') { ?>
                <tr hidden>
                    <th class="border-bottom" id="content" style="text-align: left; padding-left:15px; width:25%;">JENIS PEMERIKSAAN</th>
                    <th class="border-bottom" id="content" style="width:5%;"></th>
                    <th class="border-bottom" id="content" style="text-align: left; width:15%;">HASIL</th>
                    <th class="border-bottom" id="content" style="text-align: left; width:15%;">NILAI RUJUKAN</th>
                    <th class="border-bottom" id="content" style="text-align: left; width:10%;">SATUAN</th>
                    <th class="border-bottom" id="content" style="text-align: left; width:25%;">KETERANGAN</th>
                </tr>
            <?php } else { ?>
                <tr>
                    <th class="border-bottom" id="content" style="text-align: left; padding-left:15px; width:25%;">JENIS PEMERIKSAAN</th>
                    <th class="border-bottom" id="content" style="width:5%;"></th>
                    <th class="border-bottom" id="content" style="text-align: left; width:15%;">HASIL</th>
                    <th class="border-bottom" id="content" style="text-align: left; width:15%;">NILAI RUJUKAN</th>
                    <th class="border-bottom" id="content" style="text-align: left; width:10%;">SATUAN</th>
                    <th class="border-bottom" id="content" style="text-align: left; width:25%;">KETERANGAN</th>
                </tr>
            <?php } ?>
            <!-- END THEAD NORMAL -->

            <?php
            if ($test->test_name == 'PCR SARS COV2' || $test->test_id == '875') {
            ?>
                <!-- JIKA TEST PCR, MAKA THEAD KHUSUS -->
                <tr>
                    <th class="border-bottom" id="content" style="text-align: left; padding-left:15px; width:35%;">JENIS PEMERIKSAAN</th>
                    <th class="border-bottom" id="content" style="text-align: left; width:30%;">HASIL</th>
                    <th class="border-bottom" id="content" style="text-align: left; width:35%;">NILAI RUJUKAN</th>
                </tr>
                <!-- END THEAD KHUSUS -->
            <?php } ?>

            <?php $new_page = 1; ?>
            @endif
            @if (($group_test == '')||($group_test != $test->group_name))
            <?php $group_test = $test->group_name; ?>

            <?php
            if ($test->test_name != 'PCR SARS COV2' || $test->test_id != '875') {
            ?>
                <!-- JIKA BUKAN TEST PCR, TD NORMAL -->
                <tr id="content">
                    <td style="padding-left:15px; padding-bottom: 5px;"><span style="font-size:10px; font-style: italic; font-weight: bold">{{ $test->group_name }}</span></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <!-- END BUKAN TEST PCR, MAKA TD NORMAL -->
            <?php } else { ?>
                <!-- JIKA TEST PCR, TD KHUSUS -->
                <tr id="content">
                    <td style="padding-left:15px; padding-bottom: 5px;"><span style="font-size:10px; font-style: italic; font-weight: bold">{{ $test->group_name }}</span></td>
                    <td></td>
                    <td></td>
                </tr>
                <!-- END BUKAN TEST PCR, TD KHUSUS -->
            <?php
            }
            ?>
            <?php $row++; ?>

            @endif

            @if (($sub_group == '')||($sub_group != $test->sub_group))
            @if ($test->sub_group != '')

            <?php
            if ($test->test_name != 'PCR SARS COV2' || $test->test_id != '875') {
            ?>
                <!-- JIKA BUKAN TEST PCR, TD NORMAL -->
                <tr id="content">
                    <td style="padding-left:15px; padding-bottom: 3px;"><span style="font-size:10px;font-weight: bold">{{ $test->sub_group }}</span></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <!-- END BUKAN TEST PCR, MAKA TD NORMAL -->
            <?php } else { ?>
                <!-- JIKA TEST PCR, TD KHUSUS -->
                <tr id="content">
                    <td style="padding-left:15px; padding-bottom: 3px;"><span style="font-size:10px;font-weight: bold">{{ $test->sub_group }}</span></td>
                    <td></td>
                    <td></td>
                </tr>
                <!-- END BUKAN TEST PCR, TD KHUSUS -->
            <?php
            }
            ?>

            <?php $row++; ?>
            @endif

            <?php $sub_group = $test->sub_group; ?>

            @endif

            <?php
            if ($test->test_name != 'PCR SARS COV2' || $test->test_id != '875') {
            ?>
                <!-- JIKA BUKAN TEST PCR, TD NORMAL -->
                <tr id="content">

                    <!-- td test name -->
                    @if ($sub_group == '')
                    <td id="content" style="padding-left:25px; padding-bottom: 3px;">{{ $test->test_name }}</td>
                    @else
                    <td id="content" style="padding-left:25px; padding-bottom: 3px;">{{ $test->test_name }}</td>
                    @endif
                    <!-- end td test name -->

                    <!-- td flagging  -->
                    @if ($test->result_status_label == 'Critical')
                    <td id="content" style="text-align: right; padding-bottom: 3px;">**</td>
                    @elseif ($test->result_status_label == 'Abnormal')
                    <td id="content" style="text-align: right; padding-bottom: 3px;">*</td>
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

                    <!-- @if (strlen($test->global_result) > 15)
                    <td id="content" style="text-align: right; padding-bottom: 3px;">{!! $result !!}</td>
                    <?php $row = $row++; ?>
                    @else
                    <td id="content" style="text-align: left; padding-bottom: 3px;">{!! $result !!} </td>
                    @endif -->

                    @if ($test->result_text != null && $test->normal_value == '' && $test->unit == '')
                    <td colspan="5" id="content" style="text-align: left; padding-bottom: 3px;">{!! $result !!}</td>
                    @else
                    <td id="content" style="text-align: left; padding-bottom: 3px;">{!! $result !!}</td>
                    <td id="content" style="text-align: left; padding-bottom: 3px;">{!! $test->normal_value !!}</td>
                    <td id="content" style="text-align: left; padding-bottom: 3px;">{{ $test->unit }}</td>
                    <td id="content" style="text-align: left;">{{ $test->memo_test }}</td>
                    @endif

                    <!-- end td result -->
                </tr>
                <!-- END BUKAN TEST PCR, MAKA TD NORMAL -->
            <?php } else { ?>
                <!-- JIKA TEST PCR, TD KHUSUS -->
                <tr id="content">
                    <!-- td test name -->
                    @if ($sub_group == '')
                    <td id="content" style="padding-left:25px; padding-bottom: 3px;">{{ $test->test_name }}</td>
                    @else
                    <td id="content" style="padding-left:25px; padding-bottom: 3px;">{{ $test->test_name }}</td>
                    @endif
                    <!-- end td test name -->
                    <?php
                    $result = $test->global_result;

                    if (is_string($test->global_result)) {

                        // $result = str_replace(" ; ","<br>",$test->result ); 
                        $result = $test->global_result;
                    }
                    ?>

                    <!-- td result -->
                    @if (strlen($test->global_result) > 15)

                    <td id="content" style="text-align: right; padding-bottom: 3px;">
                        {!! $result !!}
                        <br>
                        {{ $test->memo_test }}
                    </td>
                    <?php $row = $row++; ?>

                    @else

                    <td id="content" style="text-align: left; padding-bottom: 3px;">
                        {!! $result !!}
                        <br>
                        {{ $test->memo_test }}
                    </td>

                    @endif
                    <!-- end td result -->
                    <td id="content" style="text-align: left;">
                        NEGATIF < 1,1500 <br>
                            POSITIF > 1,1500
                    </td>

                </tr>
                <!-- END BUKAN TEST PCR, TD KHUSUS -->
            <?php
            }
            ?>

            <?php $row++; ?>
            @endforeach
            </tbody>
        </table>

        <!-- End Result Table -->

        <?php  
            // JIKA BUKAN TEST PCR (VISIBILITY = VISIBLE)
            if ($visibility == 'visible') {
        ?>
                <!-- JIKA BUKAN TEST PCR, MAKA KETERANGAN NORMAL -->
                <table>
                    <tbody>
                        @if($transaction->is_print_memo != '')
                        <tr>
                            <td style="text-align: left;">Keterangan :</td>
                        </tr>
                        <tr>
                            <td style="text-align: left;">
                                <?php
                                if ($transaction->memo_result == 'hivpos') {
                                    $keterangan = [];
                                    if (($open = fopen(storage_path() . "/csv/hivpos.csv", "r")) !== FALSE) {
                                        while (($data = fgetcsv($open, ",")) !== FALSE) {
                                            $keterangan[] = $data;
                                        }
                                        fclose($open);
                                    }
                                    foreach ($keterangan as $value) {
                                        echo $value[0] . "<br>";
                                    }
                                }else if ($transaction->memo_result == 'covid19') {
                                    $keterangan = [];
                                    if (($open = fopen(storage_path() . "/csv/covid19.csv", "r")) !== FALSE) {
                                        while (($data = fgetcsv($open, ",")) !== FALSE) {
                                            $keterangan[] = $data;
                                        }
                                        fclose($open);
                                    }
                                    foreach ($keterangan as $value) {
                                        echo $value[0] . "<br>";
                                    }
                                }else if ($transaction->memo_result == 'covid19AGD1') {
                                    $keterangan = [];
                                    if (($open = fopen(storage_path() . "/csv/covid19AGD1.csv", "r")) !== FALSE) {
                                        while (($data = fgetcsv($open, ",")) !== FALSE) {
                                            $keterangan[] = $data;
                                        }
                                        fclose($open);
                                    }
                                    foreach ($keterangan as $value) {
                                        echo $value[0] . "<br>";
                                    }
                                }else if ($transaction->memo_result == 'covid19gen') {
                                    $keterangan = [];
                                    if (($open = fopen(storage_path() . "/csv/covid19gen.csv", "r")) !== FALSE) {
                                        while (($data = fgetcsv($open, ",")) !== FALSE) {
                                            $keterangan[] = $data;
                                        }
                                        fclose($open);
                                    }
                                    foreach ($keterangan as $value) {
                                        echo $value[0] . "<br>";
                                    }
                                }else if ($transaction->memo_result == 'covid19-nonreact') {
                                    $keterangan = [];
                                    if (($open = fopen(storage_path() . "/csv/covid19-nonreact.csv", "r")) !== FALSE) {
                                        while (($data = fgetcsv($open, ",")) !== FALSE) {
                                            $keterangan[] = $data;
                                        }
                                        fclose($open);
                                    }
                                    foreach ($keterangan as $value) {
                                        echo $value[0] . "<br>";
                                    }
                                }else if ($transaction->memo_result == 'covid19-react') {
                                    $keterangan = [];
                                    if (($open = fopen(storage_path() . "/csv/covid19-react.csv", "r")) !== FALSE) {
                                        while (($data = fgetcsv($open, ",")) !== FALSE) {
                                            $keterangan[] = $data;
                                        }
                                        fclose($open);
                                    }
                                    foreach ($keterangan as $value) {
                                        echo $value[0] . "<br>";
                                    }
                                }else {
                                    echo $transaction->memo_result;
                                }
                                ?>
                            </td>
                        </tr>
                        @endif

                    </tbody>
                </table>
                <!-- END BUKAN TEST PCR, MAKA KETERANGAN NORMAL -->
            <?php
            } else {
                // JIKA TEST PCR (VISIBILITY = HIDDEN)
            ?>
                <!-- JIKA BUKAN TEST PCR, MAKA KETERANGAN KHUSUS -->
                <table>
                    <tbody>
                        <tr>
                            <td style="text-align: left;"> Bahan Pemeriksaan : Swab Nasofaring</td>
                        </tr>
                        <tr>
                            <td style="text-align: left;"> Metode : Insulated Isothermal PCR (iiiPCR) </td>
                        </tr>
                        <tr>
                            <td style="text-align: left;"> <b> Catatan : </b> </td>
                        </tr>
                        <tr>
                            <td style="text-align: left;">
                                * Hasil Positif menunjukkan ditemukan material genetik SARS COV2 di dalam spesimen <br>
                                * Hasil Negatif menunjukkan tidak ditemukan material genetik SARS COV2 di dalam spesimen <br>
                                atau kadarnya belum terdeteksi oleh alat <br>
                                * Hasil pemeriksaan diatas hanya menggambarkan kondisi saat pengambilan sampel <br>
                                * Hasil TCM/PCR tidak dapat dibandingkan antara satu laboratorium lain karena perbedaan alat dan <br> target gen yang digunakan
                            </td>
                        </tr>
                    </tbody>
                </table>
                <!-- END TEST PCR, MAKA KETERANGAN KHUSUS -->
        <?php } ?>

        <table style="position: fixed;">
            <tbody>
                <tr>
                    <td width="20%" style="text-align: center;">Bandung, <?= date('d/m/Y', strtotime($print_time)); ?> </td>
                    <td width="60%" style="text-align: right;"> </td>
                    <td width="20%" style="text-align: center;"> </td>
                </tr>
                <tr>
                    <td width="20%" style="text-align: center;">Validasi Oleh,<br> 
                        @if(Auth::user()->username == 'angga')
                        <img src="{{asset('images/ttd/angga.png')}}" style="width: 65px; height: 65px"> <br>
                        @elseif(Auth::user()->username == 'dhea')
                        <img src="{{asset('images/ttd/dhea.png')}}" style="width: 65px; height: 65px"> <br>
                        @elseif(Auth::user()->username == 'dini')
                        <img src="{{asset('images/ttd/dini.png')}}" style="width: 65px; height: 65px"> <br>
                        @elseif(Auth::user()->username == 'dita')
                        <img src="{{asset('images/ttd/dita.png')}}" style="width: 65px; height: 65px"> <br>
                        @elseif(Auth::user()->username == 'any')
                        <img src="{{asset('images/ttd/dr.any.png')}}" style="width: 65px; height: 65px"> <br>
                        @elseif(Auth::user()->username == 'astri')
                        <img src="{{asset('images/ttd/dr.astri.png')}}" style="width: 65px; height: 65px"> <br>
                        @elseif(Auth::user()->username == 'eti')
                        <img src="{{asset('images/ttd/eti.png')}}" style="width: 65px; height: 65px"> <br>
                        @elseif(Auth::user()->username == 'eulis')
                        <img src="{{asset('images/ttd/eulis.png')}}" style="width: 65px; height: 65px"> <br>
                        @elseif(Auth::user()->username == 'fadholi')
                        <img src="{{asset('images/ttd/fadholi.png')}}" style="width: 65px; height: 65px"> <br>
                        @elseif(Auth::user()->username == 'herny')
                        <img src="{{asset('images/ttd/herny.png')}}" style="width: 65px; height: 65px"> <br>
                        @elseif(Auth::user()->username == 'hersa')
                        <img src="{{asset('images/ttd/hersa.png')}}" style="width: 65px; height: 65px"> <br>
                        @elseif(Auth::user()->username == 'indah')
                        <img src="{{asset('images/ttd/indah.png')}}" style="width: 65px; height: 65px"> <br>
                        @elseif(Auth::user()->username == 'indri')
                        <img src="{{asset('images/ttd/indri.png')}}" style="width: 65px; height: 65px"> <br>
                        @elseif(Auth::user()->username == 'maulia')
                        <img src="{{asset('images/ttd/maulia.png')}}" style="width: 65px; height: 65px"> <br>
                        @endif
                    </td>
                    <td width="60%" style="text-align: right;"> </td>
                    <td width="20%" style="text-align: center;">Pemeriksa,<br> 
                        @if($transaction->verficator_name == 'Angga S.')
                        <img src="{{asset('images/ttd/angga.png')}}" style="width: 65px; height: 65px"> <br>
                        @elseif($transaction->verficator_name == 'Dhea Nugraha S')
                        <img src="{{asset('images/ttd/dhea.png')}}" style="width: 65px; height: 65px"> <br>
                        @elseif($transaction->verficator_name == 'Dini Nur Islami R')
                        <img src="{{asset('images/ttd/dini.png')}}" style="width: 65px; height: 65px"> <br>
                        @elseif($transaction->verficator_name == 'Dita Fauziah')
                        <img src="{{asset('images/ttd/dita.png')}}" style="width: 65px; height: 65px"> <br>
                        @elseif($transaction->verficator_name == 'dr. Any Yuliani, Sp.PK')
                        <img src="{{asset('images/ttd/dr.any.png')}}" style="width: 65px; height: 65px"> <br>
                        @elseif($transaction->verficator_name == 'dr.astri')
                        <img src="{{asset('images/ttd/dr.astri.png')}}" style="width: 65px; height: 65px"> <br>
                        @elseif($transaction->verficator_name == 'Eti Rahmawati')
                        <img src="{{asset('images/ttd/eti.png')}}" style="width: 65px; height: 65px"> <br>
                        @elseif($transaction->verficator_name == 'Eulis Wanridah')
                        <img src="{{asset('images/ttd/eulis.png')}}" style="width: 65px; height: 65px"> <br>
                        @elseif($transaction->verficator_name == 'Fadholi')
                        <img src="{{asset('images/ttd/fadholi.png')}}" style="width: 65px; height: 65px"> <br>
                        @elseif($transaction->verficator_name == 'Herny Yoanudin')
                        <img src="{{asset('images/ttd/herny.png')}}" style="width: 65px; height: 65px"> <br>
                        @elseif($transaction->verficator_name == 'Hersa Eka Juniar')
                        <img src="{{asset('images/ttd/hersa.png')}}" style="width: 65px; height: 65px"> <br>
                        @elseif($transaction->verficator_name == 'Indah Pratiwi R')
                        <img src="{{asset('images/ttd/indah.png')}}" style="width: 65px; height: 65px"> <br>
                        @elseif($transaction->verficator_name == 'Indriana Dewi')
                        <img src="{{asset('images/ttd/indri.png')}}" style="width: 65px; height: 65px"> <br>
                        @elseif($transaction->verficator_name == 'Maulia Aussy')
                        <img src="{{asset('images/ttd/maulia.png')}}" style="width: 65px; height: 65px"> <br>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td width="20%" style="text-align: center;"> ({{ Auth::user()->name  }}) </td>
                    <td width="60%" style="text-align: right;"> </td>
                    <td width="20%" style="text-align: center;"> ({{$transaction->verficator_name}}) </td>
                </tr>

            </tbody>
        </table>
    </div>

</body>

</html>