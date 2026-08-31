<?php

namespace App\Filament\Resources\Concerns;

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
            Forms\Components\Section::make('المعلومات الشخصية')->schema([
                Forms\Components\TextInput::make('full_name')->label('الاسم الكامل')->required()->maxLength(255),
                Forms\Components\TextInput::make('email')->label('البريد')->email()->required()->maxLength(255),
                Forms\Components\TextInput::make('country_code')->label('رمز الدولة')->maxLength(8),
                Forms\Components\TextInput::make('phone')->label('الهاتف')->required()->maxLength(40),
            ])->columns(2),
            Forms\Components\Section::make('تفاصيل المحتوى')->schema([
                Forms\Components\TagsInput::make('content_types')->label('أنواع المحتوى')->required(),
                Forms\Components\TextInput::make('followers_count')->label('عدد المتابعين')->numeric()->minValue(0)->required(),
                Forms\Components\Textarea::make('content_bio')->label('نبذة المحتوى')->rows(4)->required()->columnSpanFull(),
            ])->columns(2),
            Forms\Components\Section::make('مواقع التواصل')->schema([
                Forms\Components\Repeater::make('socials')
                    ->label('')
                    ->schema([
                        Forms\Components\Select::make('platform')
                            ->label('المنصة')
                            ->options(array_combine(CreatorJoinRequest::PLATFORMS, CreatorJoinRequest::PLATFORMS))
                            ->required(),
                        Forms\Components\TextInput::make('url')->label('الرابط')->url()->required()->maxLength(500),
                    ])
                    ->columns(2)
                    ->minItems(1)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('notes')->label('ملاحظات إضافية')->columnSpanFull(),
            ]),
            Forms\Components\Section::make('الحالة')->schema([
                Forms\Components\Placeholder::make('status_display')
                    ->label('الحالة')
                    ->content(fn (?CreatorJoinRequest $record) => match ($record?->status) {
                        'pending' => 'بانتظار المراجعة',
                        'approved' => 'مقبول',
                        'rejected' => 'مرفوض',
                        default => '—',
                    }),
                Forms\Components\Textarea::make('admin_note')->label('ملاحظة الإدارة')->columnSpanFull(),
            ])->columns(1),
        ]);
    }

    public static function joinRequestInfolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('المعلومات الشخصية')->schema([
                Infolists\Components\TextEntry::make('full_name')->label('الاسم الكامل'),
                Infolists\Components\TextEntry::make('email')->label('البريد'),
                Infolists\Components\TextEntry::make('country_code')->label('رمز الدولة'),
                Infolists\Components\TextEntry::make('phone')->label('الهاتف'),
            ])->columns(2),
            Infolists\Components\Section::make('تفاصيل المحتوى')->schema([
                Infolists\Components\TextEntry::make('content_types')
                    ->label('أنواع المحتوى')
                    ->formatStateUsing(fn ($state) => is_array($state) ? implode('، ', $state) : $state),
                Infolists\Components\TextEntry::make('followers_count')->label('عدد المتابعين')->numeric(),
                Infolists\Components\TextEntry::make('content_bio')->label('نبذة المحتوى')->columnSpanFull(),
            ])->columns(2),
            Infolists\Components\Section::make('مواقع التواصل')->schema([
                Infolists\Components\RepeatableEntry::make('socials')
                    ->label('')
                    ->schema([
                        Infolists\Components\TextEntry::make('platform')->label('المنصة'),
                        Infolists\Components\TextEntry::make('url')->label('الرابط')->url(fn ($state) => $state),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Infolists\Components\TextEntry::make('notes')->label('ملاحظات إضافية')->columnSpanFull(),
            ]),
            Infolists\Components\Section::make('الحالة')->schema([
                Infolists\Components\TextEntry::make('status')->label('الحالة')
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'pending' => 'بانتظار المراجعة',
                        'approved' => 'مقبول',
                        'rejected' => 'مرفوض',
                        default => $state,
                    }),
                Infolists\Components\TextEntry::make('created_at')->label('تاريخ الطلب')->dateTime(),
                Infolists\Components\TextEntry::make('admin_note')->label('ملاحظة الإدارة')->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function joinRequestTable(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name')->label('الاسم')->searchable(),
                Tables\Columns\TextColumn::make('email')->label('البريد')->searchable(),
                Tables\Columns\TextColumn::make('phone')->label('الهاتف'),
                Tables\Columns\TextColumn::make('followers_count')->label('المتابعون')->numeric()->sortable(),
                Tables\Columns\BadgeColumn::make('status')->label('الحالة')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'pending' => 'بانتظار المراجعة',
                        'approved' => 'مقبول',
                        'rejected' => 'مرفوض',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('created_at')->label('التاريخ')->dateTime('Y-m-d')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending' => 'بانتظار المراجعة',
                        'approved' => 'مقبول',
                        'rejected' => 'مرفوض',
                    ]),
            ])
            ->actions(static::joinRequestTableActions())
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->modalHeading('حذف الطلبات المحددة')
                        ->modalDescription('ستُحذف الطلبات وحسابات المستخدمين وملفات صنّاع المحتوى المرتبطة بها.'),
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
                ->label('قبول')
                ->color('success')
                ->icon('heroicon-o-check')
                ->visible(fn (CreatorJoinRequest $record) => $record->status === 'pending')
                ->requiresConfirmation()
                ->action(function (CreatorJoinRequest $record) {
                    try {
                        $service = app(CreatorJoinRequestService::class);
                        $service->approve($record, auth()->id());
                    } catch (ValidationException $e) {
                        Notification::make()
                            ->title(collect($e->errors())->flatten()->first() ?: 'تعذر قبول الطلب')
                            ->danger()
                            ->send();

                        return;
                    }

                    if ($service->lastEmailError) {
                        Notification::make()
                            ->title('تم قبول الطلب وإنشاء الحساب، لكن تعذر إرسال البريد')
                            ->body($service->lastEmailError)
                            ->warning()
                            ->send();

                        return;
                    }

                    Notification::make()->title('تم قبول الطلب ونقل البيانات إلى حساب صانع المحتوى')->success()->send();
                }),
            Tables\Actions\Action::make('reject')
                ->label('رفض')
                ->color('danger')
                ->icon('heroicon-o-x-mark')
                ->visible(fn (CreatorJoinRequest $record) => $record->status === 'pending')
                ->form([
                    Forms\Components\Textarea::make('admin_note')->label('سبب الرفض')->required(),
                ])
                ->action(function (CreatorJoinRequest $record, array $data) {
                    app(CreatorJoinRequestService::class)->changeStatus(
                        $record,
                        'rejected',
                        auth()->id(),
                        $data['admin_note'] ?? null,
                    );
                    Notification::make()->title('تم رفض الطلب')->success()->send();
                }),
            Tables\Actions\DeleteAction::make()
                ->modalHeading('حذف طلب الانضمام')
                ->modalDescription('سيُحذف الطلب وحساب المستخدم وملف صانع المحتوى المرتبطين به.'),
        ];
    }
}
