<?php

namespace App\Filament\Resources\Concerns;

use App\Filament\Resources\CreatorResource;
use App\Models\CreatorJoinRequest;
use App\Services\CreatorJoinRequestService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Validation\ValidationException;

trait ManagesCreatorJoinRequestResource
{
    public static function joinRequestForm(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('المعلومات الشخصية'))->schema([
                Forms\Components\TextInput::make('full_name')->label(__('الاسم الكامل'))->required()->maxLength(255),
                Forms\Components\TextInput::make('email')->label(__('البريد'))->email()->required()->maxLength(255),
                Forms\Components\TextInput::make('country_code')->label(__('رمز الدولة'))->maxLength(8),
                Forms\Components\TextInput::make('phone')->label(__('الهاتف'))->required()->maxLength(40),
            ])->columns(2),
            Forms\Components\Section::make(__('تفاصيل المحتوى'))->schema([
                Forms\Components\TagsInput::make('content_types')->label(__('أنواع المحتوى'))->required(),
                Forms\Components\TextInput::make('followers_count')->label(__('عدد المتابعين'))->numeric()->minValue(0)->required(),
                Forms\Components\Textarea::make('content_bio')->label(__('نبذة المحتوى'))->rows(4)->required()->columnSpanFull(),
            ])->columns(2),
            Forms\Components\Section::make(__('مواقع التواصل'))->schema([
                Forms\Components\Repeater::make('socials')
                    ->label('')
                    ->schema([
                        Forms\Components\Select::make('platform')
                            ->label(__('المنصة'))
                            ->options(array_combine(CreatorJoinRequest::PLATFORMS, CreatorJoinRequest::PLATFORMS))
                            ->required(),
                        Forms\Components\TextInput::make('url')->label(__('الرابط'))->url()->required()->maxLength(500),
                    ])
                    ->columns(2)
                    ->minItems(1)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('notes')->label(__('ملاحظات إضافية'))->columnSpanFull(),
            ]),
            Forms\Components\Section::make(__('الحالة'))->schema([
                Forms\Components\Placeholder::make('status_display')
                    ->label(__('الحالة'))
                    ->content(fn (?CreatorJoinRequest $record) => match ($record?->status) {
                        'pending' => __('بانتظار المراجعة'),
                        'approved' => __('مقبول'),
                        'rejected' => __('مرفوض'),
                        default => '—',
                    }),
                Forms\Components\Textarea::make('admin_note')->label(__('ملاحظة الإدارة'))->columnSpanFull(),
            ])->columns(1),
        ]);
    }

    public static function joinRequestInfolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make(__('المعلومات الشخصية'))->schema([
                Infolists\Components\TextEntry::make('full_name')->label(__('الاسم الكامل')),
                Infolists\Components\TextEntry::make('email')->label(__('البريد')),
                Infolists\Components\TextEntry::make('country_code')->label(__('رمز الدولة')),
                Infolists\Components\TextEntry::make('phone')->label(__('الهاتف')),
            ])->columns(2),
            Infolists\Components\Section::make(__('تفاصيل المحتوى'))->schema([
                Infolists\Components\TextEntry::make('content_types')
                    ->label(__('أنواع المحتوى'))
                    ->formatStateUsing(fn ($state) => is_array($state) ? implode('، ', $state) : $state),
                Infolists\Components\TextEntry::make('followers_count')->label(__('عدد المتابعين'))->numeric(),
                Infolists\Components\TextEntry::make('content_bio')->label(__('نبذة المحتوى'))->columnSpanFull(),
            ])->columns(2),
            Infolists\Components\Section::make(__('مواقع التواصل'))->schema([
                Infolists\Components\RepeatableEntry::make('socials')
                    ->label('')
                    ->schema([
                        Infolists\Components\TextEntry::make('platform')->label(__('المنصة')),
                        Infolists\Components\TextEntry::make('url')->label(__('الرابط'))->url(fn ($state) => $state),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Infolists\Components\TextEntry::make('notes')->label(__('ملاحظات إضافية'))->columnSpanFull(),
            ]),
            Infolists\Components\Section::make(__('الحالة'))->schema([
                Infolists\Components\TextEntry::make('status')->label(__('الحالة'))
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'pending' => __('بانتظار المراجعة'),
                        'approved' => __('مقبول'),
                        'rejected' => __('مرفوض'),
                        default => $state,
                    }),
                Infolists\Components\TextEntry::make('created_at')->label(__('تاريخ الطلب'))->dateTime(),
                Infolists\Components\TextEntry::make('admin_note')->label(__('ملاحظة الإدارة'))->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function joinRequestTable(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name')->label(__('الاسم'))->searchable(),
                Tables\Columns\TextColumn::make('email')->label(__('البريد'))->searchable(),
                Tables\Columns\TextColumn::make('phone')->label(__('الهاتف')),
                Tables\Columns\TextColumn::make('followers_count')->label(__('المتابعون'))->numeric()->sortable(),
                Tables\Columns\BadgeColumn::make('status')->label(__('الحالة'))
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'pending' => __('بانتظار المراجعة'),
                        'approved' => __('مقبول'),
                        'rejected' => __('مرفوض'),
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('created_at')->label(__('التاريخ'))->dateTime('Y-m-d')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('الحالة'))
                    ->options([
                        'pending' => __('بانتظار المراجعة'),
                        'approved' => __('مقبول'),
                        'rejected' => __('مرفوض'),
                    ]),
            ])
            ->actions(static::joinRequestTableActions())
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->modalHeading(__('حذف الطلبات المحددة'))
                        ->modalDescription(__('ستُحذف الطلبات وحسابات المستخدمين وملفات صنّاع المحتوى المرتبطة بها.')),
                ]),
            ]);
    }

    /**
     * @return array<int, Tables\Actions\Action|Tables\Actions\ViewAction|Tables\Actions\EditAction|Tables\Actions\DeleteAction>
     */
    protected static function joinRequestTableActions(): array
    {
        return [
            Tables\Actions\ViewAction::make(),
            Tables\Actions\EditAction::make(),
            Tables\Actions\Action::make('approve')
                ->label(__('قبول'))
                ->color('success')
                ->icon('heroicon-o-check')
                ->visible(fn (CreatorJoinRequest $record) => $record->status === 'pending')
                ->requiresConfirmation()
                ->action(function (CreatorJoinRequest $record) {
                    try {
                        $service = app(CreatorJoinRequestService::class);
                        $creator = $service->approve($record, auth()->id());
                    } catch (ValidationException $e) {
                        Notification::make()
                            ->title(collect($e->errors())->flatten()->first() ?: 'تعذر قبول الطلب')
                            ->danger()
                            ->send();

                        return;
                    }

                    if ($service->lastEmailError) {
                        Notification::make()
                            ->title(__('تم قبول الطلب وإنشاء الحساب، لكن تعذر إرسال البريد'))
                            ->body($service->lastEmailError)
                            ->warning()
                            ->send();

                        return redirect(CreatorResource::getUrl('edit', ['record' => $creator]));
                    }

                    Notification::make()
                        ->title(__('تم قبول الطلب — أكمل الملف (الصورة، روابط التواصل…)'))
                        ->success()
                        ->send();

                    return redirect(CreatorResource::getUrl('edit', ['record' => $creator]));
                }),
            Tables\Actions\Action::make('reject')
                ->label(__('رفض'))
                ->color('danger')
                ->icon('heroicon-o-x-mark')
                ->visible(fn (CreatorJoinRequest $record) => $record->status === 'pending')
                ->form([
                    Forms\Components\Textarea::make('admin_note')->label(__('سبب الرفض'))->required(),
                ])
                ->action(function (CreatorJoinRequest $record, array $data) {
                    app(CreatorJoinRequestService::class)->changeStatus(
                        $record,
                        'rejected',
                        auth()->id(),
                        $data['admin_note'] ?? null,
                    );
                    Notification::make()->title(__('تم رفض الطلب'))->success()->send();
                }),
            Tables\Actions\DeleteAction::make()
                ->modalHeading(__('حذف طلب الانضمام'))
                ->modalDescription(__('سيُحذف الطلب وحساب المستخدم وملف صانع المحتوى المرتبطين به.')),
        ];
    }
}
