<?php     

namespace App\Http\Livewire\Tables;

use App\Notification;
use App\Refund;
use App\User;
use Illuminate\Support\Facades\DB;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\DateColumn;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;

class NotificationTable extends LivewireDatatable
{
    public function builder()
    {
        $query = Notification::query();
        return $query;
    }

    public function columns()
    {
        return [
            Column::callback('user_id','receiver')
                ->label(__('web/messages.refund.receiver'))
                ->sortBy('notifications.user_id')
                ->searchable(),
            Column::name('title')
                ->label(__('web/messages.title'))
                ->sortBy('notifications.title')
                ->searchable(),
            Column::callback('is_read','seen')
                ->label(__('web/messages.seen'))
                ->sortBy('notifications.is_read')
                ->searchable(),
            DateColumn::raw('created_at')
                ->label(__('web/messages.createdAt'))
                ->format('d/m/Y H:i A')
                ->sortBy(DB::raw('DATE_FORMAT(notifications.created_at, "%m%d%Y")')),
            Column::callback('uuid','detail')
                ->label(__('web/messages.detail'))
                ->searchable(),

        ];
    }

    public function receiver($userId){
        $user = User::findOrFail($userId);
        return '<a style="color:#1000ff" href="User/'.$user->uuid.'">'.$user->profile->name.'</a>' ?? '';
    }

    public function seen($isRead){
        return $isRead == 1?__('web/messages.yes'):__('web/messages.no');
    }

    public function detail($id){
        return '<a style="color:#1000ff" href="'.$id.'"><i class="feather icon-eye"></i></a>' ?? '';
    }
}
