@extends('layouts.dashboard')

@section('content')
    <div class="row">
        <div class="col-md-12">
            @if($flagged->count())
            <table class="table table-bordered">

                <tr>
                    <th>Raised By</th>
                    <th>Subject</th>
                    <th>@lang('app.job_action')</th>
                </tr>

                @foreach($flagged as $flag)
                    <tr>
                        <td>
                            {{$flag->service->user->name}}
                        </td>
                        <td>
                            <a href="{{route('view_job', $flag->service->id)}}" data-toggle="tooltip" title="View job" target="_blank">
                                {{$flag->service->category}}
                            </a> job
                            <p class="text-muted">{!! ucfirst(str_replace('_', ' ', $flag->reason)) !!}</p>
                            <p class="text-muted">
                                {{$flag->created_at->format(get_option('date_format'))}} {{$flag->created_at->format(get_option('time_format'))}}
                            </p>
                        </td>
                        {{-- <td>
                            <a href='' class="btn btn-sm btn-success" data-toggle='modal' data-tagert='#msg'{{$flag->id}}><span class='fa fa-eye'></span></a>"
                            {!! \Illuminate\Support\Str::limit(nl2br($flag->message), 20, 
                            $end="...<a href='' data-toggle='modal' data-tagert='#msg'".{{$flag->id}}." style='color: #38c172'><span class='fa fa-eye'></span></a>") 
                            !!} 
                        </td> --}}
                        <td>

                            <p>
                            <a href="" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#msg{{$flag->id}}">
                                <i class="la la-envelope"></i> view message</a>
                            
                                <a href="" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#reply{{$flag->service->id}}">
                                    <i class="la la-reply"></i> reply</a>
                        </td>
                    </tr>
                @endforeach

            </table>


            {!! $flagged->links() !!}
                
                {{-- Message Modal --}}
                @foreach($flagged as $flag)
                <div class="modal fade" id="msg{{$flag->id}}" tabindex="-1" role="dialog" aria-labelledby="{{$flag->id}}" aria-hidden="true">
                  <div class="modal-dialog" role="document">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title">Flag raised by {{$flag->service->user->name}} for {{$flag->service->category}} job</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                      </div>
                      <div class="modal-body">
                        <div class="container-fluid">
                            <table class="table table-bordered table-striped">
                                <tr>
                                    <th>Message</th>
                                    <td>{{ $flag->message }}</td>
                                </tr>
                            </table>
                          </div>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                      </div>
                    </div>
                  </div>
                </div>
                @endforeach
                {{-- Message Modal ends --}}

                {{-- Reply Modal --}}
                @foreach($flagged as $flag)
                <div class="modal fade" id="reply{{$flag->service->id}}" tabindex="-1" role="dialog" aria-labelledby="{{$flag->id}}" aria-hidden="true">
                  <div class="modal-dialog" role="document">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title">Reply To: {{$flag->service->user->name}}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                      </div>
                      <div class="modal-body">
                        <div class="container-fluid">
                            <form action="{{route('reply-flag')}}" method="post" class="form{{$flag->id}}">
                              @csrf
                              <div class="form-group row">
                                <label for="category_name" class="col-sm-4 control-label">Recepient</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" value="{{ $flag->service->user->name }}" disabled>
                                    </div>
                                </div>
                              <div class="form-group row {{ $errors->has('reply')? 'has-error':'' }}">
                                <label for="category_name" class="col-sm-4 control-label">Reply Message</label>
                                    <div class="col-sm-8">
                                        <textarea class="form-control {{e_form_invalid_class('reply', $errors)}}" id="reply" value="{{ old('reply') }}" name="reply"></textarea>

                                        {!! e_form_error('reply', $errors) !!}
                                    </div>
                                </div>
                              <input type="hidden" name="service_id" value="{{$flag->service->id}}">
                              <button type="submit" class="btn btn-success reply-btn">Reply</button>
                            </form>
                          </div>
                      </div>
                      {{-- <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                      </div> --}}
                    </div>
                  </div>
                </div>
                @endforeach
                {{-- Reply Modal ends --}}
        @else
                <div class="row">
                    <div class="col-md-12">
                        <div class="no data-wrap py-5 my-5 text-center">
                            <h1 class="display-1"><i class="la la-frown-o"></i> </h1>
                            <h1>No data available here</h1>
                        </div>
                    </div>
                </div>
        @endif

        </div>
    </div>



@endsection