@extends('layouts.default')
@section('content')
<div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12">
        <p class="lead">Create repeated expenses to keep track of long-term planned expenses</p>
    </div>
</div>

{{Form::open(['class' => 'form-horizontal','url' => route('piggybanks.update',$piggybank->id)])}}

<div class="row">
    <div class="col-lg-6 col-md-12 col-sm-6">
        <h4>Mandatory fields</h4>


        <div class="form-group">
            <label for="name" class="col-sm-4 control-label">Name</label>
            <div class="col-sm-8">
                <input type="text" name="name" class="form-control" id="name" value="{{Input::old('name') ?: $piggybank->name}}" placeholder="Name">
                @if($errors->has('name'))
                    <p class="text-danger">{{$errors->first('name')}}</p>
                @else
                    <span class="help-block">For example: new bike, new camera</span>
                @endif
            </div>
        </div>

        <div class="form-group">
            <label for="account_id" class="col-sm-4 control-label">
                Saving account
            </label>
            <div class="col-sm-8">
                {{Form::select('account_id',$accounts,Input::old('account_id') ?: $piggybank->account_id,['class' => 'form-control'])}}
                @if($errors->has('account_id'))
                <p class="text-danger">{{$errors->first('account_id')}}</p>
                @else
                <span class="help-block">Indicate on which account you've got your savings.</span>
                @endif
            </div>
        </div>

        <div class="form-group">
            {{ Form::label('targetamount', 'Target amount', ['class' => 'col-sm-4 control-label'])}}
            <div class="col-sm-8">
                <div class="input-group">
                    <span class="input-group-addon">&euro;</span>
                    {{Form::input('number','targetamount', Input::old('targetamount') ?: $piggybank->targetamount, ['step' => 'any', 'min' => '1', 'class' => 'form-control'])}}
                </div>

                @if($errors->has('targetamount'))
                <p class="text-danger">{{$errors->first('targetamount')}}</p>
                @else
                <span class="help-block">How much money do you need to save?</span>
                @endif
            </div>
        </div>

        <div class="form-group">
            {{ Form::label('targetdate', 'Target date', ['class' => 'col-sm-4 control-label'])}}
            <div class="col-sm-8">
                @if(is_null($piggybank->targetdate))
                <input type="date" name="targetdate" value="{{Input::old('targetdate') ?: ''}}"
                       class="form-control"/>
                @else
                <input type="date" name="targetdate" value="{{Input::old('targetdate') ?: $piggybank->targetdate->format('Y-m-d')}}"
                       class="form-control"/>
                @endif
                @if($errors->has('targetdate'))
                <p class="text-danger">{{$errors->first('targetdate')}}</p>
                @else
                <span class="help-block">A deadline is needed to properly repeat this repeated expesnse.</span>
                @endif
            </div>
        </div>

        <div class="form-group">
            {{ Form::label('rep_every', 'Repeat every', ['class' => 'col-sm-4 control-label'])}}
            <div class="col-sm-8">
                <input type="number" step="1" min="1" value="{{Input::old('rep_every') ?: $piggybank->rep_every}}" style="width:50px;display:inline;" max="100" name="rep_every" class="form-control" />

                <select class="form-control" name="rep_length" style="width:150px;display: inline">
                    @foreach($periods as $period)
                    @if($period == $piggybank->rep_length || Input::old('reminder') == $period)
                        <option value="{{$period}}" label="{{$period}}" selected="selected">{{$period}}</option>
                    @else
                        <option value="{{$period}}" label="{{$period}}">{{$period}}</option>
                    @endif
                    @endforeach
                </select>
                @if($errors->has('rep_length'))
                <p class="text-danger">{{$errors->first('rep_length')}}</p>
                @else
                <span class="help-block">Something about every X years bla bla bla.</span>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-6 col-md-12 col-sm-6">

            <h4>Optional fields</h4>
        <div class="form-group">
            {{ Form::label('startdate', 'Start date', ['class' => 'col-sm-4 control-label'])}}
            <div class="col-sm-8">
                @if(is_null($piggybank->startdate))
                <input type="date" name="startdate" value="{{Input::old('startdate') ?: ''}}"
                       class="form-control"/>
                @else
                <input type="date" name="startdate" value="{{Input::old('startdate') ?: $piggybank->startdate->format('Y-m-d')}}"
                       class="form-control"/>
                @endif
                @if($errors->has('startdate'))
                <p class="text-danger">{{$errors->first('startdate')}}</p>
                @else
                <span class="help-block">This date indicates when you start(ed) saving money for this piggy bank. This field defaults to today and you should keep it on today.</span>
                @endif
            </div>
        </div>
        <div class="form-group">
            {{ Form::label('reminder', 'Remind you every', ['class' => 'col-sm-4 control-label'])}}
            <div class="col-sm-8">
                <input type="number" step="1" min="1" value="{{Input::old('reminder_skip') ?: $piggybank->reminder_skip}}" style="width:50px;display:inline;" max="100" name="reminder_skip" class="form-control" />

                <select class="form-control" name="reminder" style="width:150px;display: inline">
                    <option value="none" label="do not remind me">do not remind me</option>
                    @foreach($periods as $period)
                    @if($period == $piggybank->reminder)
                    <option value="{{$period}}" label="{{$period}}" selected="selected">{{$period}}</option>
                    @else
                        <option value="{{$period}}" label="{{$period}}">{{$period}}</option>
                    @endif
                    @endforeach
                </select>
                @if($errors->has('reminder'))
                <p class="text-danger">{{$errors->first('reminder')}}</p>
                @else
                <span class="help-block">Enter a number and a period and Firefly will remind you to save money
                    for this repeated expense every now and then.</span>
                @endif
            </div>
        </div>




    </div>
</div>

<div class="row">
    <div class="col-lg-6 col-md-12 col-sm-6">

        <div class="form-group">
            <div class="col-sm-offset-4 col-sm-8">
                <button type="submit" class="btn btn-default btn-success">Update the repeated expense</button>
            </div>
        </div>
    </div>
</div>

{{Form::close()}}
@stop
@section('scripts')
<?php // echo javascript_include_tag('piggybanks-create'); ?>
@stop
