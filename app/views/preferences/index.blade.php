@extends('layouts.default')
@section('content')
{{ Breadcrumbs::renderIfExists(Route::getCurrentRoute()->getName()) }}
<!-- form -->
{{Form::open(['class' => 'form-horizontal','id' => 'preferences'])}}

<div class="row">
    <div class="col-lg-6 col-md-6 col-sm-6">
        <div class="panel panel-default">
          <div class="panel-heading">
                <i class="fa fa-credit-card fa-fw"></i> Home screen accounts
            </div>
            <div class="panel-body">
                <p class="text-info">Which accounts should be displayed on the home page?</p>
                 @foreach($accounts as $account)
                    <div class="form-group">
                        <div class="col-sm-10">
                            <div class="checkbox">
                                <label>
                                    @if(in_array($account->id,$frontPageAccounts->data) || count($frontPageAccounts->data) == 0)
                                        <input type="checkbox" name="frontPageAccounts[]" value="{{$account->id}}" checked> {{{$account->name}}}
                                    @else
                                    <input type="checkbox" name="frontPageAccounts[]" value="{{$account->id}}"> {{{$account->name}}}
                                    @endif
                                </label>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="fa fa-credit-card fa-fw"></i> Budget settings
            </div>
            <div class="panel-body">
                <p class="text-info">
                    What's the maximum amount of money a budget envelope may contain?
                </p>
                {{Form::ffAmount('budgetMaximum',$budgetMaximum,['label' => 'Budget maximum'])}}
            </div>
        </div>

    </div>
    <div class="col-lg-6 col-md-6 col-sm-6">
        <div class="panel panel-default">
          <div class="panel-heading">
                <i class="fa fa-clock-o fa-fw"></i> Home view range
            </div>
            <div class="panel-body">
                <p class="text-info">By default, Firefly will show you one month of data.</p>
                <div class="radio">
                    <label>
                        <input type="radio" name="viewRange" value="1D" @if($viewRange == '1D') checked @endif>
                        One day
                    </label>
                </div>

                <div class="radio">
                    <label>
                        <input type="radio" name="viewRange" value="1W" @if($viewRange == '1W') checked @endif>
                        One week
                    </label>
                </div>

                <div class="radio">
                    <label>
                        <input type="radio" name="viewRange" value="1M" @if($viewRange == '1M') checked @endif>
                        One month
                    </label>
                </div>

                <div class="radio">
                    <label>
                        <input type="radio" name="viewRange" value="3M" @if($viewRange == '3M') checked @endif>
                        Three months
                    </label>
                </div>

                <div class="radio">
                    <label>
                        <input type="radio" name="viewRange" value="6M" @if($viewRange == '6M') checked @endif>
                        Six months
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12">
        <div class="form-group">
            <div class="col-sm-12">
                <button type="submit" class="btn btn-success btn-lg">Save settings</button>
            </div>
        </div>
    </div>
</div>

<!-- form close -->
{{Form::close()}}

@stop
@section('scripts')
@stop
