@extends('dashboard.partials.master')

@section('content')
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">

            <div class="d-flex flex-column gap-5 gap-lg-10">
                <!--begin::Student Card-->
                <div class="card card-flush ">
                    <div class="card-header">
                        <div class="card-title">
                            <h2>{{ __('Student Details') }}</h2>
                        </div>
                        <div
                            class="form-check form-switch form-check-custom form-check-solid d-flex align-items-center justify-content-between">
                            <label class="form-check-label me-5">
                                {{ __('Block Student') }}
                            </label>
                            <input class="form-check-input {{ $student->block_flag ? 'bg-danger border-danger' : '' }}"
                                type="checkbox" id="statusSwitch" data-id="{{ $student->id }}"
                                {{ $student->block_flag ? 'checked' : '' }}>
                        </div>



                    </div>
                    <div class="card-body  ">
                        <!--begin::Row-->
                        <div class="row">
                            <!--begin::Image-->
                            <div class="col-md-2 text-start">
                                <img src="{{ $student->full_image_path }}" alt="Student Image"
                                    class="img-fluid rounded w-150px h-150px object-fit-cover" />
                            </div>
                            <!--end::Image-->

                            <!--begin::Details-->
                            <div class="col-md-10">
                                <table class="table table-row-bordered align-middle">
                                    <tbody class="fw-semibold text-gray-600">
                                        <tr>
                                            <td class="text-muted">{{ __('Full Name') }}</td>
                                            <td class="text-end">{{ $student->first_name }} {{ $student->middle_name }}
                                                {{ $student->last_name }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">{{ __('Email') }}</td>
                                            <td class="text-end">{{ $student->email }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">{{ __('Phone') }}</td>
                                            <td class="text-end">{{ $student->phone }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">{{ __('Parent phone') }}</td>
                                            <td class="text-end">{{ $student->parent_phone }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">{{ __('Parent job') }}</td>
                                            <td class="text-end">{{ $student->parent_job }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">{{ __('Gender') }}</td>
                                            <td class="text-end">{{ __(ucfirst($student->gender)) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">{{ __('Government') }}</td>
                                            <td class="text-end">{{ $student->government->name ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">{{ __('Category') }}</td>
                                            <td class="text-end">{{ $student->category->name ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">{{ __('Created At') }}</td>
                                            <td class="text-end">{{ $student->created_at->format('Y-m-d H:i') }}</td>
                                        </tr>

                                    </tbody>
                                </table>
                            </div>
                            <!--end::Details-->
                        </div>
                        <!--end::Row-->
                    </div>
                </div>
                <!--end::Student Card-->
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#statusSwitch').on('change', function() {
                var $checkbox = $(this);
                var studentId = $checkbox.data('id');

                $.ajax({
                    url: `/dashboard/students/blocking/${studentId}`,
                    type: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if ($checkbox.is(':checked')) {
                            $checkbox.addClass('bg-danger border-danger');
                            toastr.success(`${ __('Student blocked successfully') }`);
                        } else {
                            $checkbox.removeClass('bg-danger border-danger');
                            toastr.success(`${ __('Student unblocked successfully') }`);

                        }
                    },
                    error: function() {
                        $checkbox.prop('checked', !$checkbox.is(':checked')); // rollback
                        toastr.error('Something went wrong');
                    }
                });
            });
        });
    </script>
@endpush
