<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>注文完了</title>
    <style>
        table {
            width: 60%;
            border-collapse: collapse;
        }

        th, td {
            padding: 8px;
            text-align: left;
            border: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .ebook-title {
            max-width: 240px;
        }
    </style>
</head>
<body>
    <p>{{ $order->user->name }} 様</p>

    <p>ご注文ありがとうございます。</p>
    <p>注文の詳細をご確認ください。</p>

    <table>
        <tr>
            <th>No.</th>
            <th>タイトル</th>
            <th>価格</th>
        </tr>
        @foreach ($order->orderDetails as $i => $detail)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td class="ebook-title">{{ $detail->ebook_title }}</td>
                <td>{{ number_format($detail->price) }} 円</td>
            </tr>
        @endforeach
    </table>

    <p>小計: {{ number_format($order->sub_total) }} 円</p>
    <p>割引: {{ number_format($order->discount) }} 円</p>
    <p>総計: {{ number_format($order->total) }} 円</p>
</body>
</html>
