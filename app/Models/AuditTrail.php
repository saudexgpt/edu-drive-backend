<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditTrail extends Model
{
    //
    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id', 'id');
    }

    public function addEvent($request)
    {


        $id = $request->actor_id; //this is the id of the user in users table
        $name = $request->actor_name;
        $role = $request->actor_role;
        $school_id = $request->school_id;
        $sess_id = $request->sess_id;
        $term_id = $request->term_id;
        $class_teacher_id = $request->class_teacher_id;
        $actor_action = $request->action;


        $audit_trail = new AuditTrail();

        $audit_trail->school_id = $school_id;
        $audit_trail->sess_id = $sess_id;
        $audit_trail->term_id = $term_id;
        $audit_trail->class_teacher_id = $class_teacher_id;
        $audit_trail->actor_id = $id;
        $audit_trail->role = $role;
        $audit_trail->name = $name;
        $audit_trail->action_details = $actor_action;
        $audit_trail->save();
    }
}
