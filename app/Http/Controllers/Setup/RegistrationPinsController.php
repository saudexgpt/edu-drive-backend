<?php

namespace Modules\Core\Http\Controllers;

use App\RegistrationPin;
use App\School;
use Illuminate\Http\Request;
use Laracasts\Flash\Flash;
use App\Http\Controllers\Controller;
class RegistrationPinsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    
    public function staffPins()
    {
        //
        $school_id = $this->getSchool()->id;
        $reg_pins = RegistrationPin::where('school_id', $this->getSchool()->id)->orderBy('status')->get(); 

        return $this->render('pins.admin_generate_staff_pin', compact('school_id', 'reg_pins'));
    }

    public function managePins()
    {
        //
        $reg_pins = RegistrationPin::get(); 
        return $this->render('pins.manage', compact('reg_pins'));
    }

    public function generatePin($type)
    {   
        $schools = School::get();
        $school_array = [];
        if ($schools != '[]') {

            foreach ($schools as $school) {
                $school_array[$school->id] = $school->name;
            }
        }
        return $this->render('pins.generate', compact('type', 'school_array'));
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $quantity = $request->quantity;
        $type = $request->pin_type;
        $school_id = $request->school_id;
        
        for ($i = 1; $i <= $quantity; $i++) {
        
            $pin = randomCode();

            $reg_pin = RegistrationPin::where('pin', $pin)->first();

            if (!$reg_pin) {
                $reg_pin = new RegistrationPin();
                $reg_pin->school_id = $school_id;
                $reg_pin->pin_type = $type;
                $reg_pin->pin = $pin;
                $reg_pin->status = 'unused';//unused, used or given
                $reg_pin->save();
            }
            
            
        }
        Flash::success('Pins generated successfully');
       return redirect()->route('staff_pin');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\RegistrationPin  $registrationPin
     * @return \Illuminate\Http\Response
     */
    public function show(RegistrationPin $registrationPin)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\RegistrationPin  $registrationPin
     * @return \Illuminate\Http\Response
     */
    public function edit(RegistrationPin $registrationPin)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\RegistrationPin  $registrationPin
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, RegistrationPin $registrationPin)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\RegistrationPin  $registrationPin
     * @return \Illuminate\Http\Response
     */
    public function destroy(RegistrationPin $registrationPin)
    {
        //
    }
}
