<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Thống kê doanh thu</title>

    <style>
        * {
            font-family: DejaVu Sans, sans-serif;
        }

        td,
        th {
            border: 1px solid #ddd;
            padding: 8px;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>

    <div class="main">
        <div class="title">
            <h3> THỐNG KÊ DOANH THU {{$yearNow}}</h3>
        </div>

        <div>
            <div>
                <h4>
                    Doanh thu theo từng tháng trong năm {{$yearNow}}
                </h4>
                <div><img src="https://quickchart.io/chart?w=350&h=250&c={{ $lineChart }}" /></div>
            </div>
        </div>

        <div style="margin-top: 15px;">
            <div>
                <h4>Doanh thu 3 năm gần nhất</h4>
            </div>
            <div style="position: relative;">
                <table style="position:absolute; width:350px; top:20px">
                    <thead style="background-color:#04AA6D;">
                        <tr>
                            <th>Năm</th>
                            <td>Doanh Thu</td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th>{{$yearNow}}</th>
                            <td>{{number_format($revenueByYears[0], 0, '', '.')}}</td>
                        </tr>
                        <tr>
                            <th>{{$yearNow-1}}</th>
                            <td>{{number_format($revenueByYears[1], 0, '', '.')}}</td>
                        </tr>
                        <tr>
                            <th>{{$yearNow-2}}</th>
                            <td>{{number_format($revenueByYears[2], 0, '', '.')}}</td>
                        </tr>
                    </tbody>
                </table>
                <div style="position:absolute; right:0">
                    <div>
                        <h5 style="text-align: center;">Số lượng đơn hàng 3 năm gần nhất</h5>
                    </div>
                    <img src="https://quickchart.io/chart?w=150&h=150&c={{ $barChart }}" />
                </div>
            </div>
        </div>
    </div>
</body>

</html>