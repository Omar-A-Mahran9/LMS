@extends('dashboard.partials.master')

@section('content')
    <div class="container mt-5">

        <div class="row">
            <!-- Left column: width 3 -->
            <div class="col-md-3">
                <div class="p-3 border rounded bg-light">
                    <h5 class="mb-5">{{ __('Video Info') }}</h5>

                    <p><strong>{{ __('Title') }}:</strong><br> {{ $video->title ?? 'N/A' }}</p>

                    @if (!empty($video->duration_seconds))
                        <p><strong>{{ __('Duration') }}:</strong><br>
                            {{ intdiv($video->duration_seconds, 60) }}m {{ $video->duration_seconds % 60 }}s
                        </p>
                    @endif

                    <p><strong>{{ __('Views') }}:</strong><br> {{ $video->views ?? 0 }}</p>

                    <p><strong>{{ __('Preview') }}:</strong><br>
                        @if ($video->is_preview)
                            <span class="badge bg-success">{{ __('Yes') }}</span>
                        @else
                            <span class="badge bg-secondary">{{ __('No') }}</span>
                        @endif
                    </p>

                    <p><strong>{{ __('Status') }}:</strong><br>
                        @if ($video->is_active)
                            <span class="badge bg-primary">{{ __('Active') }}</span>
                        @else
                            <span class="badge bg-danger">{{ __('Inactive') }}</span>
                        @endif
                    </p>

                    @if (isset($video->created_at))
                        <p><strong>{{ __('Uploaded on') }}:</strong><br> {{ $video->created_at->format('d M Y') }}</p>
                    @endif
                </div>
            </div>


            <!-- Right column: width 9 -->
            <div class="col-md-9">
                @php
                    function getYoutubeVideoId($url)
                    {
                        $parsed_url = parse_url($url);

                        if (!isset($parsed_url['host'])) {
                            return null;
                        }

                        $host = $parsed_url['host'];

                        if (strpos($host, 'youtu.be') !== false) {
                            return trim($parsed_url['path'], '/');
                        }

                        if (strpos($host, 'youtube.com') !== false) {
                            if (isset($parsed_url['path'])) {
                                if ($parsed_url['path'] == '/watch') {
                                    parse_str($parsed_url['query'] ?? '', $query);
                                    return $query['v'] ?? null;
                                }
                                if (strpos($parsed_url['path'], '/embed/') === 0) {
                                    $videoPart = substr($parsed_url['path'], strlen('/embed/'));
                                    $videoId = explode('?', $videoPart)[0];
                                    return $videoId;
                                }
                            }
                        }

                        return null;
                    }

                    $videoId = getYoutubeVideoId($video->video_url);
                @endphp

                @if ($videoId)
                    <div class="video-responsive mb-3">
                        <iframe src="https://www.youtube.com/embed/{{ $videoId }}" frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            allowfullscreen>
                        </iframe>
                    </div>
                @else
                    <p>Invalid YouTube URL</p>
                @endif


            </div>
        </div>
    </div>
@endsection

<style>
    .video-responsive {
        position: relative;
        padding-bottom: 56.25%;
        /* 16:9 aspect ratio */
        border-radius: 25px;
        height: 0;
        overflow: hidden;
        max-width: 100%;
    }

    .video-responsive iframe {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }
</style>
