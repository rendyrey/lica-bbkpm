<!DOCTYPE html>
<html>

<head>
    <!-- <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/> -->
    <title></title>
    <style type="text/css">
        body {
            font-family: "Arial", sans-serif;
            /*font-family: 'Courier New', monospace;*/
            font-size: 13px;
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
            border-collapse: collapse;
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
            padding: 5px;
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
            height: 80px;
            width: 80px; */
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
        <br>
        <table>
            <tr>
                <td width="40%"></td>
                <td width="60%">
                    <table style="border: 1px solid black;">
                        <tr>
                            <td width="15%">Yth.</td>
                            <td width="1%">:</td>
                            <td width="50%">{{ $transaction->doctor_name }}</td>
                        </tr>
                        <tr>
                            <td>Bagian</td>
                            <td>:</td>
                            <td>{{ $transaction->room_name }}</td>
                        </tr>
                        <tr>
                            <td>RS/Klinik/Puskesmas</td>
                            <td>:</td>
                            <td>BBKPM Bandung</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <br>
        <br>
        <table style="border: 1px solid black; padding: 5px">
            <tr>
                <td width="13%" style="border-bottom: 1px solid black; border-right: 1px solid black; padding: 5px">Nomor Sediaan</td>
                <td width="17%">Nama Pasien</td>
                <td width="1%">:</td>
                <td width="25%" style="border-right: 1px solid black">{{ $transaction->patient_name }}</td>
                <td width="13%" style="border-bottom: 1px solid black; border-right: 1px solid black;">Diterima Tanggal</td>
                <td width="12%" style="border-bottom: 1px solid black;">Dijawab Tanggal</td>
            </tr>
            <tr>
                <td rowspan="2" style="border-right: 1px solid black; text-align: center">{!! $nomor_sediaan !!}</td>
                <td>Nomor Rekam Medik</td>
                <td>:</td>
                <td style="border-right: 1px solid black;">{{ $transaction->patient_medrec }}</td>
                <td rowspan="2" style="border-right: 1px solid black; text-align: center"><?= date('d/m/Y', strtotime($transaction->created_time)); ?> </td>
                <td rowspan="2" style="text-align: center">{{ $jawab_tanggal }}</td>
            </tr>
            <tr>
                <td>Jenis Kelamin/Umur</td>
                <td>:</td>
                <td style="border-right: 1px solid black;"> @if($transaction->patient_gender == 'M') L @else P @endif / {{$age}}</td>
            </tr>
        </table>
        <br>
        <table>
            @foreach ($tests as $test)
            <tr >
                <td width="15%" style="padding-top : 15px;">{!! $test->test_name !!}</td>
                <td style="padding-top : 15px;">:</td> 
           
            </tr>
     
            <tr>
                <td colspan="2" style="padding-top : -65px;">
                    {!! $test->result_text !!}

                <span style="font-size : 12px;"> {!! $test->result_label !!} </span> 

                </td>
            </tr>
            @endforeach
        </table>

        <br>
        <!-- Hasil TCM dari @if($group_name == 'FNAB') aspirat {{$group_name}} (terlampir) : @else efusi {{$group_name}} (terlampir) : @endif -->
        @if($transaction->is_print_memo != '')
        <p>Keterangan :</p>
        <p>{{  $transaction->memo_result }}</p>
        @endif

        <table style="position: fixed;">
            <tbody>
                <tr>
                    <td width="60%" style="text-align: right;"> </td>
                    <td width="40%" style="text-align: center;">Bandung, <?= date('d/m/Y', strtotime($print_time)); ?> </td>
                </tr>
                <tr>
                    <td width="60%" style="text-align: right;"> </td>
                    <td width="40%" style="text-align: center;"><br><br><br><br></td>
                </tr>
                <tr>
                    <td width="60%" style="text-align: right;"> </td>
                    <td width="40%" style="text-align: center;">
                        <u>dr. Yulie Erida NR. Sp.PA</u>
                        <br>
                        <br>
                        SIP. No. 0011/IPFK-DS/VII/2020/DPMPTSP
                    </td>
                </tr>

            </tbody>
        </table>

    </div>


</body>

</html>