@extends('dashboard.partials.master')

@section('content')
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">

            <div class="d-flex flex-column gap-5 gap-lg-10">
                <!--begin::Contact Card-->
                <div class="card card-flush">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div class="card-title">
                            <h2>{{ __('Contact Message Details') }}</h2>
                        </div>

                        @if (!$contact->reply)
                            <span class="badge badge-light-warning fs-6 px-4 py-2">
                                {{ __('Pending Reply') }}
                            </span>
                        @else
                            <span class="badge badge-light-success fs-6 px-4 py-2">
                                {{ __('Replied') }}
                            </span>
                        @endif

                    </div>

                    <div class="card-body">
                        <div class="row">
                            <!--begin::Details-->
                            <div class="col-md-12">
                                <table class="table table-row-bordered align-middle">
                                    <tbody class="fw-semibold text-gray-600">
                                        <tr>
                                            <td class="text-muted">{{ __('Full Name') }}</td>
                                            <td class="text-end">{{ $contact->full_name }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">{{ __('Email') }}</td>
                                            <td class="text-end">{{ $contact->email }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">{{ __('Phone') }}</td>
                                            <td class="text-end">{{ $contact->phone }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">{{ __('student name') }}</td>
                                            <td class="text-end">{{ $contact->student->name ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">{{ __('student email') }}</td>
                                            <td class="text-end">{{ $contact->student->email ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">{{ __('Message') }}</td>
                                            <td class="text-end">{{ $contact->description }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">{{ __('Reply') }}</td>
                                            <td class="text-end">
                                                @if ($contact->reply)
                                                    @if (Str::endsWith($contact->reply, '.mp3'))
                                                        <audio controls>
                                                            <source src="{{ $contact->full_audio_path }}"
                                                                type="audio/mpeg">
                                                            {{ __('Your browser does not support the audio element.') }}
                                                        </audio>
                                                    @else
                                                        {{ $contact->reply }}
                                                    @endif
                                                    <br>
                                                @else
                                                    <span
                                                        class="badge badge-light-warning">{{ __('Not Replied Yet') }}</span>
                                                @endif
                                            </td>
                                        </tr>

                                        <tr>
                                            <td class="text-muted">{{ __('Created At') }}</td>
                                            <td class="text-end">{{ $contact->created_at }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <!--end::Details-->
                        </div>
                    </div>
                </div>
                <!--end::Contact Card-->
                @if (!$contact->reply)
                    <div class="card mt-5">
                        <div class="card-header">
                            <h3 class="card-title">{{ __('Send a Reply') }}</h3>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('dashboard.contact.reply', $contact->id) }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                {{-- Text Reply --}}
                                <div class="form-group mb-3">
                                    <label for="reply" class="form-label">{{ __('Reply Text') }}</label>
                                    <textarea name="reply" id="reply" rows="5" class="form-control">{{ old('reply') }}</textarea>
                                    @error('reply')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Audio Reply --}}
                                <div class="form-group mb-3">
                                    <label for="reply" class="form-label">{{ __('Reply Audio') }}</label>
                                    <input type="file" name="reply" id="reply" class="form-control"
                                        accept="audio/*">
                                    @error('reply')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i> {{ __('Send Reply') }}
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="card mt-5">
                        <div class="card-header">
                            <h3 class="card-title">{{ __('Reply') }}</h3>
                        </div>
                        <div class="card-body">
                            @if ($contact->reply)
                                @if (Str::endsWith($contact->reply, '.mp3'))
                                    <audio controls>
                                        <source src="{{ asset('uploads/replies/' . $contact->reply) }}" type="audio/mpeg">
                                        {{ __('Your browser does not support the audio element.') }}
                                    </audio>
                                @else
                                    <p><strong>{{ __('Text Reply:') }}</strong> {{ $contact->reply }}</p>
                                @endif
                            @endif
                        </div>
                    </div>
                @endif


            </div>
        </div>
    </div>
@endsection
