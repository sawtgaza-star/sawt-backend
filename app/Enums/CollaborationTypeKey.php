<?php

namespace App\Enums;

enum CollaborationTypeKey: string
{
    case Creator = 'creator';
    case Sponsorship = 'sponsorship';
    case Partnership = 'partnership';
    case Other = 'other';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function labelAr(): string
    {
        return match ($this) {
            self::Creator => 'صانع محتوى',
            self::Sponsorship => 'رعاية أو تمويل',
            self::Partnership => 'شراكة استراتيجية',
            self::Other => 'تعاون آخر',
        };
    }
}
