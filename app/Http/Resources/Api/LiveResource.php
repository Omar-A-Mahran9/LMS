<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;

/**
 * Full live details with the stream / meeting links: only for students who can open the class.
 * The links are sent from JOIN_EARLY_MINUTES before the start until the end.
 */
class LiveResource extends LivesResource
{
    public function toArray(Request $request): array
    {
        $canJoin = $this->canJoin();
        $youtubeId = $this->platform === 'youtube' ? $this->youtubeId() : null;

        return parent::toArray($request) + [
            'description'      => $this->description,
            'can_join'         => $canJoin,
            'join_opens_at'    => $this->joinOpensAt()?->toIso8601String(),
            // Played inside the page (YouTube / Facebook); null => open join_url in a new tab / the app
            'player_url'       => $canJoin ? $this->playerUrl() : null,
            'join_url'         => $canJoin ? $this->embed_url : null,
            'youtube_id'       => $canJoin ? $youtubeId : null,
            'meeting_id'       => $canJoin ? $this->meeting_id : null,
            'password'         => $canJoin ? $this->password : null,
            // YouTube chat is built by the website (it needs the site's domain); other platforms use the admin's link
            'chat_embed_url'   => $canJoin && $this->chat_enabled && !$youtubeId ? $this->chat_embed_url : null,
            'is_recorded'      => (bool) $this->is_recorded,
            // kept for older clients
            'video_embed_url'  => $canJoin ? ($this->playerUrl() ?? $this->embed_url) : null,
        ];
    }
}
