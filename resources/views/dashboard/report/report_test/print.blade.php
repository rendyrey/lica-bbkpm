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
        <h3 style="text-align: center">LAPORAN BULANAN PEMERIKSAAN LABORATORIUM</h3>
        <h3 style="text-align: center; margin-top: -15px">BALAI BESAR KESEHATAN PARU MASYARAKAT BANDUNG</h3>

        <table>
            <tr>
                <td width="15%">Periode Tanggal</td>
                <td width="1%"> : </td>
                <td width="84%"> {{ $startDate }} - {{ $endDate }} </td>
            </tr>
        </table>

        <br>

        <table id="tb_result" style="border: 1px solid black; margin: 5px; border-collapse: collapse;">
            <thead>
                <tr>
                    <th class="border-bottom" style="text-align: center;">Jenis Pemeriksaan</th>
                    <th class="border-bottom" style="text-align: center;">Umum</th>
                    <th class="border-bottom" style="text-align: center;">BPJS</th>
                    <th class="border-bottom" style="text-align: center;">Total</th>
                </tr>
            </thead>
            <tbody>
                @php
                $index = 1;
                $group_test = '';
                $sub_group = '';
                $total_package = 0;
                $total_test = 0;
                $total_umum = 0;
                $total_bpjs = 0;
                $grand_total = 0;
                @endphp
                @foreach($testData as $test)

                @php
                $total_package = $test->jumlah_package_umum + $test->jumlah_package_bpjs;
                $total_test = $test->jumlah_test_umum + $test->jumlah_test_bpjs;
                @endphp

                <!-- Group Name -->
                @if (($group_test == '')||($group_test != $test->group_name))
                <?php $group_test = $test->group_name; ?>
                <tr>
                    <td colspan="4" style="text-align: left; padding-left: 10px; border: 1px solid black; border-collapse: collapse; font-weight:bold">{{ $test->group_name }}</td>
                </tr>
                @endif
                <!-- End Group Name -->

                <!-- Sub Group -->
                @if (($sub_group == '')||($sub_group != $test->sub_group))

                @if ($test->sub_group != '')
                <tr>
                    <td colspan="4" style="text-align: left; padding-left: 30px; border: 1px solid black; border-collapse: collapse; font-weight:bold">{{ $test->sub_group }}</td>
                </tr>
                @endif

                <?php $sub_group = $test->sub_group; ?>
                @endif
                <!-- End Sub Group -->

                <!-- Package/Test Name , Insurance, Total  -->
                <tr>
                    <td style="text-align: left; padding-left: 50px; border: 1px solid black; border-collapse: collapse;">
                        <?php
                        if ($test->package_name) {
                            echo $test->package_name;
                        } else {
                            echo $test->test_name;
                        }
                        ?>
                    </td>
                    <td style="text-align: center; border: 1px solid black; border-collapse: collapse;">
                        <?php
                        if ($test->package_name) {
                            if ($test->jumlah_package_umum != 0) {
                                echo $test->jumlah_package_umum;
                            } else {
                                echo '-';
                            }
                        } else {
                            if ($test->jumlah_test_umum != 0) {
                                echo $test->jumlah_test_umum;
                            } else {
                                echo '-';
                            }
                        }
                        ?>
                    </td>
                    <td style="text-align: center; border: 1px solid black; border-collapse: collapse;">
                        <?php
                        if ($test->package_name) {
                            if ($test->jumlah_package_bpjs != 0) {
                                echo $test->jumlah_package_bpjs;
                            } else {
                                echo '-';
                            }
                        } else {
                            if ($test->jumlah_test_bpjs != 0) {
                                echo $test->jumlah_test_bpjs;
                            } else {
                                echo '-';
                            }
                        }
                        ?>
                    </td>
                    <td style="text-align: center; border: 1px solid black; border-collapse: collapse;">
                        <?php
                        if ($test->package_name) {
                            echo $total_package;
                        } else {
                            echo $total_test;
                        }
                        $grand_total += $total_package + $total_test;
                        ?>
                    </td>
                </tr>
                <!-- End Package/Test Name -->

                @php
                $index++;
                $total_umum += $test->jumlah_package_umum +$test->jumlah_test_umum;
                $total_bpjs += $test->jumlah_package_bpjs +$test->jumlah_test_bpjs;
                @endphp
                @endforeach
            </tbody>
            <tfooter>
                <th style="text-align: right; padding-right: 10px">Jumlah Pemeriksaan</th>
                <th style="text-align: center">{{ $total_umum }}</th>
                <th style="text-align: center">{{ $total_bpjs }}</th>
                <th style="text-align: center">{{ $grand_total }}</th>
            </tfooter>
        </table>
</body>

</html>