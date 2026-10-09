<?php

namespace App\Enums;

enum DesignCategory: string
{
    case FloorPlan = 'floor_plan';
    case TwoDDrawing = '2d_drawing';
    case ThreeDDesign = '3d_design';
    case Elevation = 'elevation';
    case Section = 'section';
    case Furniture = 'furniture';
    case Ceiling = 'ceiling';
    case ElectricalLayout = 'electrical_layout';
    case Lighting = 'lighting';
    case Rendering = 'rendering';
    case Animation = 'animation';
    case Walkthrough = 'walkthrough';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::FloorPlan => 'Floor Plan',
            self::TwoDDrawing => '2D Drawing',
            self::ThreeDDesign => '3D Design',
            self::Elevation => 'Elevation',
            self::Section => 'Section',
            self::Furniture => 'Furniture',
            self::Ceiling => 'Ceiling',
            self::ElectricalLayout => 'Electrical Layout',
            self::Lighting => 'Lighting',
            self::Rendering => 'Rendering',
            self::Animation => 'Animation',
            self::Walkthrough => 'Walkthrough',
            self::Other => 'Other',
        };
    }

    public static function options(): array
    {
        return array_combine(
            array_map(fn (self $case) => $case->value, self::cases()),
            array_map(fn (self $case) => $case->label(), self::cases()),
        );
    }
}
