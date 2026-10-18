<?php

namespace App\Filament\Resources\Posts;

use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Models\Post;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedNewspaper;

    protected static string | UnitEnum | null $navigationGroup = 'محتوا';

    protected static ?string $modelLabel = 'مقاله';

    protected static ?string $pluralModelLabel = 'مقالات';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Group::make([
                Section::make()->schema([
                    TextInput::make('title')
                        ->label('عنوان')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        // Fill the address automatically from the title for new articles.
                        ->afterStateUpdated(function (Set $set, Get $get, ?string $state, string $operation): void {
                            if ($operation === 'create' && blank($get('slug'))) {
                                $set('slug', Post::makeSlug((string) $state));
                            }
                        }),
                    TextInput::make('slug')
                        ->label('آدرس (slug)')
                        ->helperText('بخش آخر آدرس مقاله در سایت. خالی بماند از عنوان ساخته می‌شود.')
                        ->maxLength(150)
                        ->dehydrateStateUsing(fn (?string $state) => filled($state) ? Post::makeSlug($state) : null),
                    Textarea::make('excerpt')
                        ->label('خلاصه')
                        ->helperText('در فهرست مقالات نمایش داده می‌شود؛ دو سه جمله کافی است.')
                        ->rows(3)
                        ->maxLength(500),
                    RichEditor::make('body')
                        ->label('متن مقاله')
                        ->toolbarButtons([
                            ['bold', 'italic', 'underline', 'link'],
                            ['h2', 'h3'],
                            ['alignStart', 'alignCenter', 'alignEnd'],
                            ['blockquote', 'bulletList', 'orderedList', 'table', 'attachFiles'],
                            ['undo', 'redo'],
                        ])
                        ->fileAttachmentsDisk('public')
                        ->fileAttachmentsDirectory('blog/content')
                        ->fileAttachmentsVisibility('public')
                        ->extraInputAttributes(['style' => 'min-height: 24rem']),
                ]),
                Section::make('سئو')
                    ->description('عنوان و توضیحی که در نتایج گوگل نمایش داده می‌شود. خالی بماند، از عنوان و خلاصه استفاده می‌شود.')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextInput::make('meta_title')->label('عنوان سئو')->maxLength(70)
                            ->helperText('بهتر است کمتر از ۶۰ حرف باشد.'),
                        Textarea::make('meta_description')->label('توضیح سئو')->rows(3)->maxLength(320)
                            ->helperText('بهتر است بین ۱۲۰ تا ۱۶۰ حرف باشد.'),
                    ]),
            ])->columnSpan(['lg' => 2]),

            Group::make([
                Section::make('انتشار')->schema([
                    Select::make('locale')
                        ->label('زبان')
                        ->options(Post::LOCALES)
                        ->default('fa')
                        ->required()
                        ->native(false),
                    Select::make('status')
                        ->label('وضعیت')
                        ->options(Post::STATUSES)
                        ->default('draft')
                        ->required()
                        ->native(false),
                    DateTimePicker::make('published_at')
                        ->label('تاریخ انتشار')
                        ->helperText('خالی بماند، هنگام انتشار زمان فعلی ثبت می‌شود. تاریخ آینده یعنی انتشار زمان‌بندی‌شده.')
                        ->seconds(false)
                        ->native(false),
                ]),
                Section::make('تصویر شاخص')->schema([
                    FileUpload::make('cover_path')
                        ->hiddenLabel()
                        ->image()
                        ->imageEditor()
                        ->imageEditorAspectRatioOptions(['16:9'])
                        ->disk('public')
                        ->directory('blog/covers')
                        ->visibility('public')
                        ->maxSize(4096)
                        ->helperText('نسبت ۱۶ به ۹، حداکثر ۴ مگابایت.'),
                ]),
            ])->columnSpan(['lg' => 1]),
        ])->columns(['default' => 1, 'lg' => 3]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('published_at', 'desc')
            ->columns([
                ImageColumn::make('cover_path')->label('')->disk('public')->imageWidth(72)->imageHeight(40)->extraImgAttributes(['class' => 'rounded object-cover']),
                TextColumn::make('title')->label('عنوان')->searchable()->weight('bold')->wrap()->limit(70),
                TextColumn::make('locale')->label('زبان')->badge()
                    ->formatStateUsing(fn (string $state) => $state === 'en' ? 'EN' : 'فا')
                    ->color(fn (string $state) => $state === 'en' ? 'info' : 'gray'),
                TextColumn::make('status')->label('وضعیت')->badge()
                    ->formatStateUsing(fn (string $state) => Post::STATUSES[$state] ?? $state)
                    ->color(fn (string $state) => $state === 'published' ? 'success' : 'warning'),
                TextColumn::make('published_at')->label('تاریخ انتشار')->date('Y/m/d')->sortable()->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('locale')->label('زبان')->options(Post::LOCALES),
                SelectFilter::make('status')->label('وضعیت')->options(Post::STATUSES),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('مشاهده')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('gray')
                    ->url(fn (Post $record) => $record->url())
                    ->openUrlInNewTab()
                    ->visible(fn (Post $record) => $record->status === 'published'),
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPosts::route('/'),
            'create' => CreatePost::route('/create'),
            'edit' => EditPost::route('/{record}/edit'),
        ];
    }
}
