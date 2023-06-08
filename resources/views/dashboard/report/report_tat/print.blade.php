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
        }

        tr th {
            background: #eee;
            border: 1px solid;

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
            margin-left: 40px;
            height: 80px;
            width: 80px;
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
            margin-top: 0px
        }
    </style>
</head>

<body>

    <div>
        <h3>LAPORAN WAKTU TUNGGU HASIL PEMERIKSAAN LABORATORIUM</h3>
        <table>
            <tr>
                <td width="15%">Periode Tanggal</td>
                <td width="1%"> : </td>
                <td width="84%"> {{ $startDate }} - {{ $endDate }} </td>
            </tr>
            <tr>
                <td>Grup Pemeriksaan</td>
                <td> : </td>
                <td> {{ $group }} </td>
            </tr>
        </table>

        <br>

        <table id="tb_result" style="border: 1px solid black; margin: 5px; border-collapse: collapse;">
            <thead>
                <tr>
                    <th class="border-bottom" style="text-align: center;">No</th>
                    <th class="border-bottom" style="text-align: center;">Tanggal</th>
                    <th class="border-bottom" style="text-align: center;">Pasien</th>
                    <th class="border-bottom" style="text-align: center;">Rekam Medik</th>
                    <th class="border-bottom" style="text-align: center;">Nama Grup</th>
                    <th class="border-bottom" style="text-align: center;">Nama Paket</th>
                    <th class="border-bottom" style="text-align: center;">Nama Tes</th>
                    <th class="border-bottom" style="text-align: center;">Waktu Pendaftaran</th>
                    <th class="border-bottom" style="text-align: center;">Waktu Sampling</th>
                    <th class="border-bottom" style="text-align: center;">Waktu Selesai</th>
                    <th class="border-bottom" style="text-align: center;">Waktu TAT</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $index = 1;
                foreach ($tatData as $data) {
                    $checkin_time = \Carbon\Carbon::parse($data->checkin_time);
                    $draw_time = \Carbon\Carbon::parse($data->draw_time);
                    $validate_time = \Carbon\Carbon::parse($data->validate_time);
                    $tat_time = $validate_time->diffInSeconds($checkin_time);
                    $tat = gmdate('H:i:s', $tat_time);
                ?>
                    <tr>
                        <td style="text-align: center; border: 1px solid black; border-collapse: collapse;">{{ $index }}</td>
                        <td style="text-align: center; border: 1px solid black; border-collapse: collapse;">{{ date('d/m/Y', strtotime($data->created_time)) }}</td>
                        <td style="text-align: center; border: 1px solid black; border-collapse: collapse;">{{ $data->patient_name }}</td>
                        <td style="text-align: center; border: 1px solid black; border-collapse: collapse;">{{ $data->patient_medrec }}</td>
                        <td style="text-align: center; border: 1px solid black; border-collapse: collapse;">{{ $data->group_name }}</td>
                        <td style="text-align: center; border: 1px solid black; border-collapse: collapse;">{{ $data->package_name }}</td>
                        <td style="text-align: center; border: 1px solid black; border-collapse: collapse;">{{ $data->test_name_custom }}</td>
                        <td style="text-align: center; border: 1px solid black; border-collapse: collapse;">{{ date('H:i:s', strtotime($checkin_time)) }}</td>
                        <td style="text-align: center; border: 1px solid black; border-collapse: collapse;">{{ date('H:i:s', strtotime($draw_time)) }}</td>
                        <td style="text-align: center; border: 1px solid black; border-collapse: collapse;">{{ date('H:i:s', strtotime($validate_time)) }}</td>
                        <td style="text-align: center; border: 1px solid black; border-collapse: collapse;">{{ date('H:i:s', strtotime($tat)) }}</td>
                    </tr>
                <?php
                    $index++;
                }
                ?>

            </tbody>
        </table>
</body>

</html>