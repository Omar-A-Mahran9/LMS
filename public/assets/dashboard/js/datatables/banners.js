"use strict";

var datatable;

var KTDatatablesServerSide = function () {
    let dbTable = 'banners';

    const escapeHtml = (value) => $('<div>').text(value ?? '').html();

    const statusBadges = {
        running:   ['badge-light-success', __('Running')],
        scheduled: ['badge-light-info', __('Scheduled')],
        expired:   ['badge-light-warning', __('Expired')],
        disabled:  ['badge-light-danger', __('Disabled')],
    };

    const formatDate = (value) => value ? value.substring(0, 16).replace('T', ' ') : null;

    var initDatatable = function () {
        datatable = $("#kt_datatable").DataTable({
            language: language,
            searchDelay: searchDelay,
            processing: processing,
            serverSide: serverSide,
            order: [],
            stateSave: saveState,
            select: {
                style: 'multi',
                selector: 'td:first-child input[type="checkbox"]',
                className: 'row-selected'
            },
            ajax: {
                url: `/dashboard/${dbTable}`,
            },
            columns: [
                { data: 'id' },
                { data: 'title_ar' },
                { data: 'placement', name: 'placement' },
                { data: 'audience' },
                { data: 'starts_at' },
                { data: 'clicks' },
                { data: 'sort_order' },
                { data: 'is_active' },
                { data: null },
            ],
            columnDefs: [
                {
                    targets: 0,
                    orderable: false,
                    render: function (data) {
                        return `
                            <div class="form-check form-check-sm form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" value="${data}" />
                            </div>`;
                    }
                },
                {
                    targets: 1,
                    render: function (data, type, row) {
                        const thumb = row.full_image_path
                            ? `<div class="w-80px h-45px rounded bgi-no-repeat bgi-position-center bgi-size-cover flex-shrink-0" style="background-image:url('${row.full_image_path}')"></div>`
                            : `<div class="w-80px h-45px rounded flex-shrink-0 d-flex align-items-center justify-content-center" style="background:${row.bg_color || '#1f4e4a'};color:${row.text_color || '#fff'}"><i class="ki-outline ki-notification-on fs-2" style="color:inherit"></i></div>`;

                        return `
                            <div class="d-flex align-items-center gap-3 text-start">
                                ${thumb}
                                <div class="d-flex flex-column">
                                    <a href="/dashboard/${dbTable}/${row.id}/edit" class="text-gray-800 text-hover-primary fw-bold">${escapeHtml(data)}</a>
                                    ${row.badge_ar ? `<span class="badge badge-light-warning align-self-start mt-1">${escapeHtml(row.badge_ar)}</span>` : ''}
                                </div>
                            </div>`;
                    }
                },
                {
                    targets: 2,
                    render: (data) => `<span class="badge badge-light-primary">${bannerLabels.placements[data] ?? data}</span>`
                },
                {
                    targets: 3,
                    orderable: false,
                    render: function (data, type, row) {
                        let text = bannerLabels.audiences[data] ?? data;
                        if (row.category) text += `<div class="text-muted fs-8">${escapeHtml(row.category.name)}</div>`;
                        return `<span class="fs-7">${text}</span>`;
                    }
                },
                {
                    targets: 4,
                    render: function (data, type, row) {
                        const from = formatDate(row.starts_at);
                        const to = formatDate(row.ends_at);
                        if (!from && !to) return `<span class="text-muted fs-7">${__('Always')}</span>`;

                        return `
                            <div class="fs-7 text-start d-inline-block">
                                <div><span class="text-muted">${__('From')}:</span> ${from ?? '—'}</div>
                                <div><span class="text-muted">${__('To')}:</span> ${to ?? '—'}</div>
                                ${row.show_countdown ? `<span class="badge badge-light-dark mt-1">${__('Countdown')}</span>` : ''}
                            </div>`;
                    }
                },
                {
                    targets: 5,
                    render: (data) => `<span class="fw-bold">${Number(data).toLocaleString()}</span>`
                },
                {
                    targets: 7,
                    render: function (data, type, row) {
                        const [cls, label] = statusBadges[row.schedule_status] ?? statusBadges.disabled;
                        return `
                            <div class="d-flex flex-column align-items-center gap-2">
                                <label class="form-check form-switch form-check-custom form-check-solid">
                                    <input class="form-check-input h-20px w-35px" type="checkbox" data-toggle-banner="${row.id}" ${data ? 'checked' : ''}>
                                </label>
                                <span class="badge ${cls}">${label}</span>
                            </div>`;
                    }
                },
                {
                    targets: -1,
                    data: null,
                    orderable: false,
                    render: function (data) {
                        return `
                            <div class="d-flex justify-content-center gap-2">
                                <a href="/dashboard/${dbTable}/${data.id}/edit" class="btn btn-icon btn-light-primary btn-sm" title="${__('Edit')}">
                                    <i class="ki-outline ki-pencil fs-4"></i>
                                </a>
                                <a href="#" class="btn btn-icon btn-light-danger btn-sm" data-kt-docs-table-filter="delete_row" title="${__('Delete')}">
                                    <i class="ki-outline ki-trash fs-4"></i>
                                </a>
                            </div>`;
                    },
                },
            ],
        });

        datatable.on('draw', function () {
            initToggleToolbar();
            toggleToolbars();
            deleteRowWithURL(`/dashboard/${dbTable}/`);
            deleteSelectedRowsWithURL({
                url: `/dashboard/${dbTable}/delete-selected`,
                restoreUrl: null
            });
        });
    }

    // On / off straight from the list
    var handleToggle = function () {
        $(document).on('change', '[data-toggle-banner]', function () {
            const input = this;
            $.ajax({
                method: 'POST',
                url: `/dashboard/${dbTable}/${input.dataset.toggleBanner}/toggle`,
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: () => {
                    showToast();
                    datatable.draw(false);
                },
                error: (response) => {
                    input.checked = !input.checked;
                    response.status === 403 ? unauthorizedAlert() : errorAlert();
                },
            });
        });
    }

    var handlePlacementFilter = function () {
        $('#placement_filter').on('change', function () {
            datatable.column(2).search(this.value).draw();
        });
    }

    return {
        init: function () {
            initDatatable();
            handleSearchDatatable();
            initToggleToolbar();
            handleToggle();
            handlePlacementFilter();
        }
    }
}();

KTUtil.onDOMContentLoaded(function () {
    KTDatatablesServerSide.init();
});
