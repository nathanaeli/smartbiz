<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'tenant_id',
        'parent_id',
        'subject',
        'body',
        'is_broadcast',
        'sent_at',
        'read_at',
        'attachment_path',
        'attachment_name',
        'attachment_type',
        'attachment_size',
        'video_url',
    ];

    protected $casts = [
        'is_broadcast' => 'boolean',
        'sent_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(Message::class, 'parent_id');
    }

    public function scopeBroadcast($query)
    {
        return $query->where('is_broadcast', true);
    }

    public function scopeForTenant($query, $tenantId)
    {
        return $query->where(function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId)
              ->orWhere('is_broadcast', true);
        });
    }

    /**
     * Check if the message has an attachment
     */
    public function hasAttachment()
    {
        return !empty($this->attachment_path);
    }

    /**
     * Get the attachment URL
     */
    public function getAttachmentUrl()
    {
        return $this->attachment_path ? asset('storage/' . $this->attachment_path) : null;
    }

    /**
     * Get formatted file size
     */
    public function getFormattedFileSize()
    {
        if (!$this->attachment_size) {
            return '';
        }

        $bytes = $this->attachment_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Check if attachment is an image
     */
    public function isImageAttachment()
    {
        return $this->attachment_type && str_starts_with($this->attachment_type, 'image/');
    }

    /**
     * Check if the message has a video URL
     */
    public function hasVideo()
    {
        return !empty($this->video_url) && $this->isValidVideoUrl();
    }

    /**
     * Validate if the video URL is from a supported platform
     */
    public function isValidVideoUrl()
    {
        if (!$this->video_url) {
            return false;
        }

        $url = strtolower($this->video_url);

        // Supported platforms
        $patterns = [
            '/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/',
            '/youtu\.be\/([a-zA-Z0-9_-]+)/',
            '/youtube\.com\/embed\/([a-zA-Z0-9_-]+)/',
            '/youtube\.com\/v\/([a-zA-Z0-9_-]+)/',
            '/vimeo\.com\/([0-9]+)/',
            '/vimeo\.com\/video\/([0-9]+)/',
            '/dailymotion\.com\/video\/([a-zA-Z0-9]+)/',
            '/dai\.ly\/([a-zA-Z0-9]+)/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the video platform name
     */
    public function getVideoPlatform()
    {
        if (!$this->video_url) {
            return null;
        }

        $url = strtolower($this->video_url);

        if (strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false) {
            return 'YouTube';
        } elseif (strpos($url, 'vimeo.com') !== false) {
            return 'Vimeo';
        } elseif (strpos($url, 'dailymotion.com') !== false || strpos($url, 'dai.ly') !== false) {
            return 'Dailymotion';
        }

        return 'Unknown';
    }

    /**
     * Get the video ID from the URL
     */
    public function getVideoId()
    {
        if (!$this->video_url) {
            return null;
        }

        $url = $this->video_url;

        // YouTube patterns
        if (preg_match('/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return $matches[1];
        } elseif (preg_match('/youtu\.be\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return $matches[1];
        } elseif (preg_match('/youtube\.com\/embed\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return $matches[1];
        } elseif (preg_match('/youtube\.com\/v\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return $matches[1];
        }

        // Vimeo patterns
        elseif (preg_match('/vimeo\.com\/([0-9]+)/', $url, $matches)) {
            return $matches[1];
        } elseif (preg_match('/vimeo\.com\/video\/([0-9]+)/', $url, $matches)) {
            return $matches[1];
        }

        // Dailymotion patterns
        elseif (preg_match('/dailymotion\.com\/video\/([a-zA-Z0-9]+)/', $url, $matches)) {
            return $matches[1];
        } elseif (preg_match('/dai\.ly\/([a-zA-Z0-9]+)/', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Generate embed HTML for the video
     */
    public function getVideoEmbedHtml($width = 560, $height = 315)
    {
        if (!$this->hasVideo()) {
            return null;
        }

        $videoId = $this->getVideoId();
        $platform = $this->getVideoPlatform();

        switch ($platform) {
            case 'YouTube':
                return '<div class="video-wrapper" style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; max-width: 100%; background: #000; border-radius: 12px; box-shadow: 0 8px 32px rgba(0,0,0,0.3);">
                            <iframe src="https://www.youtube.com/embed/' . $videoId . '?rel=0&modestbranding=1"
                                    style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;"
                                    allowfullscreen
                                    title="YouTube Video">
                            </iframe>
                        </div>';

            case 'Vimeo':
                return '<div class="video-wrapper" style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; max-width: 100%; background: #000; border-radius: 12px; box-shadow: 0 8px 32px rgba(0,0,0,0.3);">
                            <iframe src="https://player.vimeo.com/video/' . $videoId . '"
                                    style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;"
                                    allowfullscreen
                                    title="Vimeo Video">
                            </iframe>
                        </div>';

            case 'Dailymotion':
                return '<div class="video-wrapper" style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; max-width: 100%; background: #000; border-radius: 12px; box-shadow: 0 8px 32px rgba(0,0,0,0.3);">
                            <iframe src="https://www.dailymotion.com/embed/video/' . $videoId . '"
                                    style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;"
                                    allowfullscreen
                                    title="Dailymotion Video">
                            </iframe>
                        </div>';

            default:
                return '<div class="alert alert-warning">Unsupported video platform</div>';
        }
    }

    /**
     * Get video thumbnail URL
     */
    public function getVideoThumbnail()
    {
        if (!$this->hasVideo()) {
            return null;
        }

        $videoId = $this->getVideoId();
        $platform = $this->getVideoPlatform();

        switch ($platform) {
            case 'YouTube':
                return "https://img.youtube.com/vi/{$videoId}/maxresdefault.jpg";
            case 'Vimeo':
                // Vimeo thumbnails require API call, return a placeholder for now
                return "https://vumbnail.com/{$videoId}.jpg";
            case 'Dailymotion':
                return "https://www.dailymotion.com/thumbnail/video/{$videoId}";
            default:
                return null;
        }
    }

    /**
     * Mark message as read
     */
    public function markAsRead()
    {
        if (!$this->read_at) {
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * Check if message is a reply
     */
    public function isReply()
    {
        return !is_null($this->parent_id);
    }

    /**
     * Get the root message (original message in thread)
     */
    public function getRootMessage()
    {
        $message = $this;
        while ($message->parent) {
            $message = $message->parent;
        }
        return $message;
    }

    /**
     * Analyze the sentiment of the message content
     */
    public function analyzeSentiment()
    {
        $content = strtolower($this->body);

        // Positive keywords
        $positiveWords = ['good', 'great', 'excellent', 'amazing', 'wonderful', 'fantastic', 'perfect', 'awesome', 'brilliant', 'outstanding', 'thank', 'thanks', 'appreciate', 'pleased', 'happy', 'satisfied', 'love', 'like', 'best', 'superb'];

        // Negative keywords
        $negativeWords = ['bad', 'terrible', 'awful', 'horrible', 'worst', 'hate', 'disappointed', 'angry', 'frustrated', 'annoyed', 'upset', 'sad', 'sorry', 'problem', 'issue', 'error', 'fail', 'wrong', 'mistake'];

        // Urgent keywords
        $urgentWords = ['urgent', 'asap', 'emergency', 'critical', 'important', 'deadline', 'immediate', 'rush', 'quickly', 'soon'];

        $positiveCount = 0;
        $negativeCount = 0;
        $urgentCount = 0;

        $words = str_word_count($content, 1);

        foreach ($words as $word) {
            if (in_array($word, $positiveWords)) {
                $positiveCount++;
            }
            if (in_array($word, $negativeWords)) {
                $negativeCount++;
            }
            if (in_array($word, $urgentWords)) {
                $urgentCount++;
            }
        }

        // Determine sentiment
        if ($urgentCount > 0) {
            return 'Urgent';
        } elseif ($positiveCount > $negativeCount) {
            return 'Positive';
        } elseif ($negativeCount > $positiveCount) {
            return 'Negative';
        } else {
            return 'Neutral';
        }
    }
}
