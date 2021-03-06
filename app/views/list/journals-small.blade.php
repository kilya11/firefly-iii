<table class="table table-bordered">
    <?php $tableSum = 0;?>
    @foreach($journals as $journal)

    <tr>
        <td>
            <a href="{{route('transactions.show',$journal->id)}}" title="{{{$journal->description}}}">{{{$journal->description}}}</a>
        </td>
        <td>
            <?php $tableSum += floatval($journal->transactions[1]->amount);?>
            @if($journal->transactiontype->type == 'Withdrawal')
                <span class="text-danger">{{Amount::formatTransaction($journal->transactions[1],false)}}</span>
            @endif
            @if($journal->transactiontype->type == 'Deposit')
                <span class="text-success">{{Amount::formatTransaction($journal->transactions[1],false)}}</span>
            @endif
            @if($journal->transactiontype->type == 'Transfer')
                <span class="text-info">{{Amount::formatTransaction($journal->transactions[1],false)}}</span>
            @endif
        </td>
        <td>
            {{$journal->date->format('j F Y')}}
        </td>
        <td>
            @foreach($journal->transactions as $t)
                @if(floatval($t->amount < 0))
                    @if($t->account->accounttype->description == 'Cash account')
                        <span class="text-success">(cash)</span>
                    @else
                        <a href="{{route('accounts.show',$t->account_id)}}">{{{$t->account->name}}}</a>
                    @endif
                @endif
            @endforeach
        </td>
    </tr>
    @endforeach
    @if(isset($displaySum) && $displaySum === true)
    <tr>
        <td><em>Sum</em></td>
        <td colspan="3">{{Amount::format($tableSum)}}</td>

    </tr>
    @endif
</table>
