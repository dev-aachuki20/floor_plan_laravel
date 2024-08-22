<?php
namespace App\Jobs;

use App\Models\Room;
use App\Models\RotaSession;
use App\Models\RotaSessionQuarter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessRemainingQuarterDays implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $quarterId;
    protected $quarterYear;
    protected $hospitalId;
    protected $remainingDays;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $data)
    {
        $this->quarterId     = $data['quarter_id'];
        $this->quarterYear   = $data['quarter_year'];
        $this->hospitalId    = $data['hospital_id'];
        $this->remainingDays = $data['remaining_days'];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $rooms = Room::select('id', 'room_name')->where('hospital_id',$this->hospitalId)->get();
        $timeSlots = config('constant.time_slots');

        foreach ($this->remainingDays as $date) {
            $start = Carbon::parse($date);
            $dayOfWeek = $start->format('l');
            $weekNumber = $start->weekOfYear;

            foreach ($rooms as $room) {
                foreach ($timeSlots as $timeSlot) {
                    $quarterWeek = RotaSessionQuarter::where('quarter_no', $this->quarterId)
                    ->where('quarter_year', $this->quarterYear)
                    ->where('hospital_id', $this->hospitalId)
                    ->where('day_name', $dayOfWeek)
                    ->first();

                    if ($quarterWeek) {

                        // Check if the rota session already exists
                        $rotaSession = RotaSession::where('hospital_id',$this->hospitalId)
                        ->where('room_id', $this->roomId)
                        ->where('time_slot', $slotKey)
                        ->where('week_day_date', $date)
                        ->first();

                        $rotaSessionRecords = [
                            'quarter_id'      => $validatedData['quarter_id'] ?? null,
                            'hospital_id'     => $hospital_id,
                            'week_no'         => $weekNumber,
                            'room_id'         => $roomId,
                            'time_slot'       => $slotKey,
                            'speciality_id'   => $speciality ?? config('constant.unavailable_speciality_id'),
                            'week_day_date'   => $date,
                        ];


                        if ($rotaSession) {

                            if(!is_null($rotaSession->speciality_id)){

                                if($rotaSession->speciality_id != $rotaSessionRecords['speciality_id']){
                                    $isSpecialityChanged = true;

                                    $speciality_name_before_changed = $rotaSession->specialityDetail->speciality_name;
                                }

                            }else{
                                $isNewCreated = true;
                            }
                            // Update existing rota session
                            $rotaSession->update($rotaSessionRecords);

                            $rotaSession = RotaSession::find($rotaSession->id);

                        } else {
                            // Create new rota session
                            $rotaSession = RotaSession::create($rotaSessionRecords);

                            $isNewCreated = true;
                        }
                    }

                }

            }



        }
    }
}
