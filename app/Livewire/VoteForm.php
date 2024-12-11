<?php

namespace App\Livewire;

use App\Models\Checkin;
use App\Models\Employees;
use App\Models\Poll;
use App\Models\Vote;
use Exception;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Livewire\Component;
use Illuminate\Contracts\View\View;

class VoteForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];
    public string $error='';

    public $poll;
    public function mount(Poll $poll): void
    {
        $this->poll = $poll;
        // Initialize the `data` array with `poll_option_id`
        $this->data = [
            'poll_option_id' => '', // Default value
            'employee_id' => '',   // Ensure employee_id is also initialized
        ];
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
//                Forms\Components\Radio::make('poll_option_id')
//                    ->label('Select an Option')
//                    ->options(
//                        $this->poll->options->mapWithKeys(function ($option) {
//                            return [
//                                $option->id =>
//                                    '<div class="flex items-center space-x-4">' .
//                                    '<img src="' . $option->image . '" alt="' . $option->option . '" class="w-12 h-12 rounded-md object-cover">' .
//                                    '<span class="text-sm font-medium">' . $option->option . '</span>' .
//                                    '</div>',
//                            ];
//                        })->toArray()
//                    )
//                    ->required()
//                    ->reactive() // Ensure live updates when state changes
//                    ->inline(false), // Adjust display if needed
                Forms\Components\TextInput::make('employee_id')
                    ->label('Employee ID')
                    ->required()
                    ->maxLength(255),
            ])
            ->statePath('data')
            ->model(Vote::class);
    }

    public function create()
    {
        try {
            if (empty($this->data['poll_option_id'])) {
                $this->error = 'Please select a poll option';
                $this->dispatch('open-modal', id: 'error-modal');
                return;
            }
            $employee = Employees::where('employee_id', $this->data['employee_id'])->first();
            if ($employee) {
                $vote_exist = Vote::where('employee_id', $this->data['employee_id'])
                    ->whereHas('pollOption', function ($query) {
                        $query->where('poll_id', $this->poll->id);
                    })
                    ->first();
                if($vote_exist){
                    $this->error = 'Employee ID already voted for this poll';
                    $this->dispatch('open-modal', id: 'error-modal');
                    return;
                }
            }else{
                $this->error = 'Employee ID doesnt exists';
                $this->dispatch('open-modal', id: 'error-modal');
                return;
            }

            $record = Vote::create([
                'poll_id' => $this->poll->id,
                'employee_id' => $this->data['employee_id'],
                'poll_option_id'=>$this->data['poll_option_id'],
            ]);
            $this->form->model($record)->saveRelationships();
            $this->dispatch('open-modal', id: 'success-modal');

        }catch (Exception $e) {
            if($e->getMessage() == "The employee ID has already been taken."){
                $this->error="This Employee ID has already been checked-in.";
            }else{
                $this->error=$e->getMessage();
            }
            $this->dispatch('open-modal', id: 'error-modal');
        }

    }
    public function closeModal()
    {
        $this->data =[];
    }
    public function render(): View
    {
        return view('livewire.vote-form');
    }
}
