<?php

return [

'font_dir' => storage_path('fonts/'), // مسار مجلد الخطوط
'font_cache' => storage_path('fonts/'), // ذاكرة التخزين المؤقت للخط

'default_font' => 'cairo',

'font_data' => [
    'cairo' => [
        'R'  => 'Cairo-Regular.ttf', // الخط العادي
        'B'  => 'Cairo-Bold.ttf',    // الخط العريض (إن وجد)
    ],
],

];
