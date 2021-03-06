@extends('layouts.default')
@section('content')
{{ Breadcrumbs::renderIfExists(Route::getCurrentRoute()->getName(), $piggyBank) }}
{{Form::open(['class' => 'form-horizontal','id' => 'destroy','url' => route('piggy_banks.destroy',$piggyBank->id)])}}
<div class="row">
    <div class="col-lg-6 col-md-12 col-sm-12">
        <div class="panel panel-red">
            <div class="panel-heading">
                Delete piggy bank "{{{$piggyBank->name}}}"
            </div>
            <div class="panel-body">
                <p>
                Are you sure?
                </p>

                <p>
                    <button type="submit" class="btn btn-default btn-danger">Delete permanently</button>
                    <a href="{{URL::previous()}}" class="btn-default btn">Cancel</a >
                </p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="form-group">
            <div class="col-sm-8">

            </div>
        </div>
    </div>
</div>


{{Form::close()}}
@stop
