@extends('layouts.default')
@section('content')
{{ Breadcrumbs::renderIfExists(Route::getCurrentRoute()->getName(), $date) }}
<div class="row">
    <div class="col-lg-6 col-md-6 col-sm-12">
        <table class="table table-bordered table-striped">
            <tr>
                <th>Account</th>
                <th>Start of month</th>
                <th>Current balance</th>
                <th>Spent</th>
            </tr>
            @foreach($accounts as $account)
                <tr>
                    <td><a href="{{route('accounts.show',$account->id)}}">{{{$account->name}}}</a></td>
                    <td>{{mf($account->startBalance)}}</td>
                    <td>{{mf($account->endBalance)}}</td>
                    <td>{{mf($account->startBalance - $account->endBalance,false)}}</td>
                </tr>
            @endforeach
        </table>
    </div>
</div>
<div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12">

        <table class="table table-bordered table-striped">
            <tr>
                <th colspan="2">Budgets</th>
                <?php
                $accountSums = [];
                ?>
                @foreach($accounts as $account)
                    <th><a href="{{route('accounts.show',$account->id)}}">{{{$account->name}}}</a></th>
                    <?php
                        $accountSums[$account->id] = 0;
                    ?>
                @endforeach
                <th colspan="2">
                    Left in budget
                </th>
            </tr>
            @foreach($budgets as $id => $budget)
            <tr>
                <td>{{{$budget['name']}}}
                @if($id == 0)
                    <i class="fa fa-fw fa-question-circle" data-toggle="tooltip" data-placement="top" title="The calculation used here is slightly different from the row below. The numbers should match."></i>
                @endif
                </td>
                <td>{{mf($budget['amount'])}}</td>
                <?php $spent = 0;?>
                @foreach($accounts as $account)
                    @if(isset($account->budgetInformation[$id]))
                        <td>
                            @if($id == 0)
                            <a href="#">{{mf($account->budgetInformation[$id]['amount'])}}</a>
                            @else
                                {{mf($account->budgetInformation[$id]['amount'])}}
                            @endif
                        </td>
                        <?php
                        $spent += floatval($account->budgetInformation[$id]['amount']);
                        $accountSums[$account->id] += floatval($account->budgetInformation[$id]['amount']);
                        ?>
                    @else
                        <td>{{mf(0)}}</td>
                    @endif
                @endforeach
                <td>{{mf($budget['amount'] + $budget['spent'])}}</td>
                <td>{{mf($budget['amount'] + $spent)}}</td>
            </tr>
            @endforeach
            <tr>
                <td colspan="2">Without budget
                    <i class="fa fa-fw fa-question-circle" data-toggle="tooltip" data-placement="top" title="The calculation used here is slightly different from the row above. The numbers should match."></i>
                </td>
                @foreach($accounts as $account)
                    @if(isset($account->budgetInformation[0]))
                        <td>
                            <a href="#">{{mf($account->budgetInformation[0]['amount'])}}</a>
                        </td>
                    @else
                        <td>{{mf(0)}}</td>
                    @endif
                @endforeach
                <td colspan="2">&nbsp;</td>
            </tr>
            <tr>
                <td colspan="2">Balanced by transfers</td>
                @foreach($accounts as $account)
                    <td>
                        <a href="#">{{mf($account->balancedAmount)}}</a>
                    </td>
                @endforeach
                <td colspan="2">&nbsp;</td>
            </tr>
            <!--
            <tr>
                <td colspan="2">Balancing transfers</td>
                @foreach($accounts as $account)
                    <td>{{mf(0)}}</td>
                @endforeach
                <td colspan="2">&nbsp;</td>
            </tr>
            <tr>
                <td colspan="2">Income</td>
                @foreach($accounts as $account)
                    <td>{{mf(0)}}</td>
                @endforeach
                <td colspan="2">&nbsp;</td>
            </tr>
            --->
            <tr>
                <td colspan="2">Left unbalanced</td>
                @foreach($accounts as $account)
                    <?php
                    $accountSums[$account->id] += $account->balancedAmount;
                    ?>
                    @if(isset($account->budgetInformation[0]))
                        <td>
                            <a href="#">{{mf($account->budgetInformation[0]['amount'] + $account->balancedAmount)}}</a>
                        </td>
                    @else
                        <td>{{mf(0)}}</td>
                    @endif
                @endforeach
                <td colspan="2">&nbsp;</td>
            </tr>
            <tr>
                <td colspan="2"><em>Sum</em></td>
                @foreach($accounts as $account)
                    <td>{{mf($accountSums[$account->id])}}</td>
                @endforeach
                <td colspan="2">&nbsp;</td>
            </tr>
            <tr>
                <td colspan="2">Expected balance</td>
                @foreach($accounts as $account)
                    <td>{{mf($account->startBalance + $accountSums[$account->id])}}</td>
                @endforeach
                <td colspan="2">&nbsp;</td>
            </tr>

        </table>
    </div>
</div>

<!-- modal to show various budget information -->
<div class="modal fade" id="budgetModal">

</div>

@stop
@section('scripts')
{{HTML::script('assets/javascript/firefly/reports.js')}}
@stop