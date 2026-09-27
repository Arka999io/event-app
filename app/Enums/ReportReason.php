<?php

namespace App\Enums;

enum ReportReason: string
{
    case Spam = 'spam';
    case Harassment = 'harassment';
    case InappropriateContent = 'inappropriate_content';
    case Scam = 'scam';
    case Other = 'other';
}