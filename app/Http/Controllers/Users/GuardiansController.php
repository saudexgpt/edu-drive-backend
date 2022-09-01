<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;

use App\Models\Guardian;
use App\Models\Student;
use App\Models\CClass;
use App\Models\ClassTeacher;
use App\Models\StudentsInClass;
use App\Models\LocalGovernmentArea;
use App\Models\State;
use App\Http\Requests\GuardianRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;
use Laracasts\Flash\Flash;

class GuardiansController extends Controller
{

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $guardians = Guardian::with('user', 'guardianStudents')->where('school_id', $this->getSchool()->id)->get();
        return $this->render(compact('guardians'));
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        return $this->render('core::guardians.create');
    }

    /**
     * @param GuardianRequest $request
     * @param Guardian $guardian
     * @return \Illuminate\Http\RedirectResponse
     */
    // public function store(GuardianRequest $request, Guardian $guardian)
    // {
    //     $inputs = $request->all();
    //     $mime = $request->file('avatar')->getClientMimeType();
    //     $name = "guardian_" . time() . "." . $request->file('avatar')->guessClientExtension();
    //     $avatar = $request->file('avatar')->storeAs('guardians', $name, 'public');
    //     $inputs['avatar'] = $avatar;
    //     $inputs['mime'] = $mime;
    //     $inputs['school_id'] = $this->getSchool()->id;
    //     $guardian = $guardian->create($inputs);

    //     // Create parent account
    //     $guardian->user()->create([
    //         'email' => $inputs['email'],
    //         'password' => $inputs['password'],
    //         'account_holder_type' => 'parent'
    //     ]);

    //     Flash::success('Parent information added successfully');
    //     return redirect()->route('parents.index');
    // }

    // /**
    //  * @param $id
    //  * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
    //  */
    // public function edit($id)
    // {
    //     try {
    //         $guardian = Guardian::findOrFail($id);
    //         return $this->render('core::guardians.edit', compact('guardian'));
    //     } catch (ModelNotFoundException $ex) {
    //         Flash::error('Error: ' . $ex->getMessage());
    //         return redirect()->route('parents.index');
    //     }
    // }

    /**
     * @param GuardianRequest $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    // public function update(GuardianRequest $request, $id)
    // {
    //     try {
    //         $guardian = Guardian::findOrFail($id);
    //         $inputs = $request->all();

    //         if ($request->hasFile('avatar')) {
    //             // Unlink the old image
    //             Storage::disk('public')->delete($guardian->avatar);

    //             $mime = $request->file('avatar')->getClientMimeType();
    //             $name = "guardian_" . time() . "." . $request->file('avatar')->guessClientExtension();
    //             $logo = $request->file('avatar')->storeAs('guardians', $name, 'public');
    //             $inputs['avatar'] = $logo;
    //             $inputs['mime'] = $mime;
    //         }
    //         $guardian->update($inputs);
    //         if ($inputs['password'] != "") {
    //             $user = $guardian->user;
    //             $user->email = $inputs['email'];
    //             $user->password = $inputs['password'];
    //             $user->save();
    //         }
    //         Flash::success('Parent information updated successfully');
    //         return redirect()->route('parents.index');
    //     } catch (ModelNotFoundException $ex) {
    //         Flash::error('Error: ' . $ex->getMessage());
    //         return redirect()->route('parents.index');
    //     }
    // }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function show(Guardian $guardian)
    {
        $school_id = $this->getSchool()->id;
        $sess_id = $this->getSession()->id;
        $guardian = $guardian::with(['user.state', 'user.lga', 'guardianStudents.student.myClasses' => function ($query) use ($school_id, $sess_id) {
            $query->where(['sess_id' => $sess_id, 'students_in_classes.school_id' => $school_id])->orderBy('id', 'DESC');
        }, 'guardianStudents.student.myClasses.classTeacher.c_class', 'guardianStudents.student.user'])->find($guardian->id);
        return $this->render(compact('guardian'));
    }

    public function parentInfo($id = null)
    {
        $school_id = $this->getSchool()->id;
        $sess_id = $this->getSession()->id;
        $term_id = $this->getTerm()->id;

        $guardian_obj = new Guardian();
        $student_in_class_obj = new StudentsInClass();

        if ($id) {
            $view_by = 'non_parent';
            $guardian = $guardian_obj->fetchGuardianDetails($id);
        } else {
            $view_by = 'parent';
            if (!$this->getGuardian()) {
                // Flash::error('You do not have a ward in this school');
                return $this->render('errors.403');
                //return redirect()->route('dashboard');
            }
            $guardian = $guardian_obj->fetchGuardianDetails($this->getGuardian()->id);
        }
        if ($guardian === false) {
            return $this->render('errors.404');
        }
        $guardian->state = State::find($guardian->user->state_id);
        $guardian->lga = LocalGovernmentArea::find($guardian->user->lga_id);


        $wards = $guardian->wards;
        foreach ($wards as $student) {

            $student_current_class = $student_in_class_obj->fetchStudentInClass($student->id, $sess_id, $term_id, $school_id);
            if ($student_current_class) {
                if ($student_current_class->classTeacher) {
                    $class = CClass::find($student_current_class->classTeacher->class_id);
                    $student->c_class = $class;
                } else {
                    $student->c_class = '';
                }
            }
        }

        return $this->render('core::guardians.dashboard', compact('guardian', 'wards', 'view_by'));
    }

    public function guardianWards()
    {
        //$request = request()->all();
        $school_id = $this->getSchool()->id;
        $sess_id = $this->getSession()->id;
        $term_id = $this->getTerm()->id;
        $id = $this->getGuardian()->id;
        $student_in_class_obj = new StudentsInClass();
        $guardian = Guardian::with(['guardianStudents.student.user'])->find($id);

        $wards = $guardian->guardianStudents;
        foreach ($wards as $student) {

            $student_current_class = $student_in_class_obj->fetchStudentInClass($student->id, $sess_id, $term_id, $school_id);


            $student->class = ($student_current_class) ? $student_current_class->classTeacher : null;
        }

        return $this->render(compact('guardian', 'wards'));
    }
    public function wardActivities($id)
    {
        $school_id = $this->getSchool()->id;
        $sess_id = $this->getSession()->id;
        $term_id = $this->getTerm()->id;
        $student_in_class_obj = new StudentsInClass();

        $student = Student::find($id);
        $student_current_class = $student_in_class_obj->fetchStudentInClass($id, $sess_id, $term_id, $school_id);
        $student->class_teacher_id = $student_current_class->class_teacher_id;
        $student->feePaymentMonitor = $student->feePaymentMonitor()->orderBy('id', 'DESC')->get();

        return $this->render('core::guardians.ward_activities', compact('student'));
    }
}
