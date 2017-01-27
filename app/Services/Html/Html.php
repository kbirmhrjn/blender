<?php

namespace App\Services\Html;

use Exception;
use Spatie\Html\Elements\A;
use Spatie\Html\Elements\Div;
use Spatie\Html\Elements\Span;
use Spatie\Html\Elements\Textarea;

class Html extends \Spatie\Html\Html
{
    public function alert(string $type, string $message): Div
    {
        return $this->div()
            ->class(['alert', "-{$type}"])
            ->text($message);
    }

    public function flashMessage(): Div
    {
        if (! Session::has('flash_notification.message')) {
            return $this->div();
        }

        return $this->alert(
            Session::get('flash_notification.level'),
            Session::get('flash_notification.message')
        );
    }

    public function error(string $message, string $field = ''): Div
    {
        return $this->alert('danger', $message)
            ->attributeIf($field, 'data-validation-error', $field);
    }

    public function message($message): Div
    {
        return $this->alert('success', $message);
    }

    public function info($message): Div
    {
        return $this->alert(
            'info',
            $this->icon('info-circle').' '.$message
        );
    }

    public function warning($message): Div
    {
        return $this->alert(
            'warning',
            $this->icon('exclamation-triangle').' '.$message
        );
    }

    public function icon(string $icon): Span
    {
        return $this->span()->class("fa fa-{$icon}");
    }

    public function avatar(User $user): Span
    {
        return $this->span()
            ->class('avatar')
            ->attribute('style', "background-image: url('{$user->avatar}')");
    }

    public function onlineIndicator(bool $online): Span
    {
        return $this->icon($online ? 'circle' : 'circle-o')
            ->class($online ? 'on' : 'off');
    }

    public function backToIndex(string $action, array $parameters = []): A
    {
        return $this->a(
            action($action, $parameters),
            fragment('back.backToIndex')
        )->class('breadcrumb--back');
    }

    public function redactor(string $name = '', string $value = ''): Textarea
    {
        $this->ensureModelIsAvailable();

        $medialibraryUrl = action(
            'Back\Api\MediaLibraryController@add',
            [short_class_name($this->model), $this->model->id, 'redactor']
        );

        $this->textarea($name, $value)
            ->attributes([
                'data-editor',
                'data-editor-medialibrary-url' => $medialibraryUrl,
            ]);
    }

    public function datePicker(string $name = '', string $value = ''): string
    {
        return $this->text($name, $value)
            ->attribute('data-datetimepicker')
            ->class('-datetime');
    }

    public function category(string $type)
    {
        $this->ensureModelIsAvailable();

        return $this->select(
            "{$type}_tags[]",
            Tag::getWithType($type)->pluck('name', 'name'),
            $this->model->tagsWithType($type)->pluck('name', 'name')
        );
    }

    public function tags(string $type): string
    {
        return $this->category($type)
            ->attributes(['multiple', 'data-select' => 'tags']);
    }

    public function old(string $name = '', string $value = '')
    {
        $value = parent::old($name, $value);

        if ($value instanceof Carbon) {
            return $value->format('d/m/Y');
        }

        return $value;
    }

    protected function ensureModelIsAvailable()
    {
        if (empty($this->model)) {
            throw new Exception('Method requires a model to be set on the html builder');
        }
    }
}
