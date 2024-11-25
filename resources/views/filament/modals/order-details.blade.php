<div>
    <h2 class="text-xl font-bold mb-4">Order Details</h2>
    <p><strong>Order Number:</strong> {{ $record->order_number }}</p>
    <p><strong>Customer:</strong> {{ $record->customer->name }}</p>
    <p><strong>Total Price:</strong> GHS {{ number_format($record->total_price, 2) }}</p>
    <p><strong>Transaction Status:</strong> {{ ucfirst($record->transaction_status) }}</p>
    <hr class="my-4">
    <h3 class="text-lg font-bold mb-2">Order Items</h3>
    <ul>
        @foreach ($record->photos as $item)
            <li>{{ $item->title }} - GHS {{ number_format($item->price, 2) }}</li>
        @endforeach
    </ul>
    <hr class="my-4">
    <h3 class="text-lg font-bold mb-2">Transactions</h3>
    <ul>
        @foreach ($record->transactions as $transaction)
            <li>
                ID: {{ $transaction->transaction_id }},
                Amount: GHS {{ number_format($transaction->amount, 2) }},
                Status: {{ ucfirst($transaction->status) }},
                Date: {{ $transaction->transaction_date }}
            </li>
        @endforeach
    </ul>
</div>
