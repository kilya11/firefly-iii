@extends('layouts.default')
@section('content')
{{ Breadcrumbs::renderIfExists(Route::getCurrentRoute()->getName(), $repeatedExpense) }}
{{Form::model($repeatedExpense, ['class' => 'form-horizontal','id' => 'update','url' => route('repeated.update',$repeatedExpense->id)])}}

<div class="row">
    <div class="col-lg-6 col-md-12 col-sm-6">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <i class="fa fa-fw fa-exclamation"></i> Mandatory fields
            </div>
            <div class="panel-body">
                {{Form::ffText('name')}}
                {{Form::ffSelect('account_id',$accounts,null,['label' => 'Save on account'])}}
                {{Form::ffAmount('targetamount')}}
                {{Form::ffDate('targetdate',null,['label' => 'First target date'])}}
                {{Form::ffSelect('rep_length',$periods,null,['label' => 'Repeats every'])}}
                {{Form::ffInteger('rep_every',null,['label' => 'Skip period'])}}
                {{Form::ffInteger('rep_times',null,['label' => 'Repeat times'])}}

            </div>
        </div>
        <p>
            <button type="submit" class="btn btn-lg btn-success">
                <i class="fa fa-pencil"></i> Update repeated expense
            </button>
        </p>
    </div>
    <div class="col-lg-6 col-md-12 col-sm-12">
        <!-- panel for optional fields -->
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="fa fa-smile-o"></i> Optional fields
            </div>
            <div class="panel-body">
                {{Form::ffCheckbox('remind_me','1',$preFilled['remind_me'],['label' => 'Remind me'])}}
                {{Form::ffSelect('reminder',$periods,$preFilled['reminder'],['label' => 'Remind every'])}}
            </div>
        </div>

        <!-- panel for options -->
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="fa fa-bolt"></i> Options
            </div>
            <div class="panel-body">
                {{Form::ffOptionsList('update','piggy bank')}}
            </div>
        </div>

    </div>
</div>
{{--

        <h4>Mandatory fields</h4>

        <h4>Optional fields</h4>


        <div class="form-group">
            {{ Form::label('reminder', 'Remind you every', ['class' => 'col-sm-4 control-label'])}}
            <div class="col-sm-8">
                <input type="number" step="1" min="1" value="{{Input::old('reminder_skip') ?: 1}}" style="width:50px;display:inline;" max="100" name="reminder_skip" class="form-control" />

                <select class="form-control" name="reminder" style="width:150px;display: inline">
                    <option value="none" label="do not remind me">do not remind me</option>
                    @foreach($periods as $period)
                        <option value="{{$period}}" label="{{$period}}">{{$period}}</option>
                    @endforeach
                </select>
                 @if($errors->has('reminder'))
                                <p class="text-danger">{{$errors->first('reminder')}}</p>
                                @else
                <span class="help-block">Enter a number and a period and Firefly will remind you to add money
                    to this piggy bank every now and then.</span>
                    @endif
            </div>
        </div>


    </div>
</div>

<div class="row">
    <div class="col-lg-6 col-md-12 col-sm-6">

        <div class="form-group">
            <div class="col-sm-offset-4 col-sm-8">
                <button type="submit" class="btn btn-default btn-success">Create the piggy bank</button>
            </div>
        </div>
    </div>
</div>
--}}

{{Form::close()}}
@stop
