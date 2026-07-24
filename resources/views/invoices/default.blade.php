<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice->number }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            width: 100%;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .company-details {
            float: left;
        }
        .invoice-details {
            float: right;
            text-align: right;
        }
        .clear {
            clear: both;
        }
        .bill-to {
            margin-bottom: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: left;
        }
        th {
            background-color: #f8f8f8;
        }
        .text-right {
            text-align: right;
        }
        .totals {
            width: 50%;
            float: right;
        }
        .totals table {
            border: none;
        }
        .totals td, .totals th {
            border: none;
            border-bottom: 1px solid #ccc;
        }
        .status {
            font-weight: bold;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-details">
            <h2>OmniBill</h2>
            <p>123 Billing Street<br>SaaS City, Cloud 99999</p>
        </div>
        <div class="invoice-details">
            <h1>INVOICE</h1>
            <p><strong>Invoice Number:</strong> {{ $invoice->number }}</p>
            <p><strong>Date:</strong> {{ $invoice->finalized_at ? $invoice->finalized_at->format('M d, Y') : ($invoice->created_at ? $invoice->created_at->format('M d, Y') : '') }}</p>
            @if($invoice->due_date)
            <p><strong>Due Date:</strong> {{ $invoice->due_date->format('M d, Y') }}</p>
            @endif
            <p class="status"><strong>Status:</strong> {{ $invoice->status }}</p>
        </div>
        <div class="clear"></div>
    </div>

    <div class="bill-to">
        <h3>Bill To:</h3>
        <p>
            <strong>Customer ID:</strong> {{ $invoice->customer_id }}<br>
            <!-- Normally you would have customer name/email here, but OmniBill uses customer_id -->
        </p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th class="text-right">Quantity</th>
                <th class="text-right">Unit Price</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->lineItems as $item)
            <tr>
                <td>{{ $item->description }}</td>
                <td class="text-right">{{ $item->quantity }}</td>
                <td class="text-right">{{ number_format($item->unit_price / 100, 2) }} {{ strtoupper($invoice->currency) }}</td>
                <td class="text-right">{{ number_format($item->amount / 100, 2) }} {{ strtoupper($invoice->currency) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <table>
            <tr>
                <th>Subtotal:</th>
                <td class="text-right">{{ number_format($invoice->subtotal / 100, 2) }} {{ strtoupper($invoice->currency) }}</td>
            </tr>
            <tr>
                <th>Tax:</th>
                <td class="text-right">{{ number_format($invoice->tax_total / 100, 2) }} {{ strtoupper($invoice->currency) }}</td>
            </tr>
            <tr>
                <th>Total:</th>
                <td class="text-right"><strong>{{ number_format($invoice->total / 100, 2) }} {{ strtoupper($invoice->currency) }}</strong></td>
            </tr>
            <tr>
                <th>Amount Paid:</th>
                <td class="text-right">{{ number_format($invoice->amount_paid / 100, 2) }} {{ strtoupper($invoice->currency) }}</td>
            </tr>
            <tr>
                <th>Amount Due:</th>
                <td class="text-right"><strong>{{ number_format($invoice->amount_due / 100, 2) }} {{ strtoupper($invoice->currency) }}</strong></td>
            </tr>
        </table>
    </div>
    <div class="clear"></div>
</body>
</html>
