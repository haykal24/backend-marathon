<?php

return [
    /*
     * The disk on which to store added files and derived images by default.
     */
    'disk_name' => env('MEDIA_DISK', env('FILESYSTEM_DISK', 'r2')),

    /*
     * The maximum file size of an item in bytes.
     */
    'max_file_size' => 1024 * 1024 * 10, // 10MB

    /*
     * This queue connection will be used to generate derived and responsive images.
     */
    'queue_connection_name' => env('QUEUE_CONNECTION', 'sync'),

    /*
     * By default all conversions will be performed on a queue.
     */
    'queue_conversions_by_default' => env('QUEUE_CONVERSIONS_BY_DEFAULT', false),

    /*
     * The fully qualified class name of the media model.
     */
    'media_model' => Spatie\MediaLibrary\MediaCollections\Models\Media::class,

    /*
     * The path where to store temporary files while performing image conversions.
     */
    'temporary_directory_path' => null,
];