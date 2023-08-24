<?php

namespace App\Http\Controllers\Materials;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Requests\MaterialRequest;
use App\Models\Material;
use App\Models\Teacher;
use App\Models\School;
use App\Models\Staff;
use App\Models\SubjectTeacher;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Laracasts\Flash\Flash;

class MaterialsController extends Controller
{
    public function index(Material $material, Teacher $teacher)
    {
        $school = $this->getSchool();
        $teacher_id = $this->getStaff()->id;

        $materials = Material::with('subjectTeacher.subject', 'teacher.user')
            ->where('teacher_id', $teacher_id)
            ->orderBy('id', 'DESC')
            ->get();

        $teacher_subjects = $teacher->teacherSubjects($teacher_id, $school->id);

        //$materials = Material::where(['teacher_id'=> $this->staff_id, 'school_id'=>$this->school_id])->get();
        return response()->json(compact('materials', 'teacher_subjects'), 200);
    }

    public function teacherSubjectMaterials(Request $request)
    {
        $user = $this->getUser();
        $school_id = $this->getSchool()->id;
        $teacher_id = $this->getStaff()->id;
        if ($user->hasRole('admin')) {
            $subject_teachers = SubjectTeacher::with(['subject', 'classTeacher.c_class'])->where(['school_id' => $school_id])->get();
        } else {
            $subject_teachers = SubjectTeacher::with(['subject', 'classTeacher.c_class'])->where(['teacher_id' => $teacher_id, 'school_id' => $school_id])->get();
        }


        return response()->json(compact('subject_teachers'), 200);
    }

    public function create(Teacher $teacher)
    {
        $school = $this->getSchool();
        $teacher_id = $this->getStaff()->id;

        $details = $teacher->teacherSubjects($teacher_id, $school->id);

        $subject_details = subjectTeacherSelectWithClassLevel($details); //from helpers


        return $this->render('material::materials.create', compact('subject_details'));
    }
    public function uploadOnlineClassMaterials(Request $request)
    {
        $media = $request->file('media');
        $school = $this->getSchool();
        if ($media != null && $media->isValid()) {
            $file_name = time() . "." . $media->guessClientExtension();
            // $folder_key = $request->folder_key . DIRECTORY_SEPARATOR . "photo" . DIRECTORY_SEPARATOR . $role;
            $folder_key = $school->folder_key . '/' . "classroom";
            return  $this->uploadFile($media, $file_name, $folder_key);
        }
    }
    public function store(Request $request, Material $material)
    {
        // return $request;
        $school = $this->getSchool(); //new object of school

        $folder_key = $school->folder_key;
        $folder = "schools/" . $folder_key . '/materials';

        $subject_teacher_id = $request->subject_teacher_id;
        $subject_teacher = SubjectTeacher::with('classTeacher', 'subject')->find($subject_teacher_id);
        $title = $request->title;

        $teacher_id = $this->getStaff()->id;
        $inputs = $request->all();
        $extension = $request->file('material')->guessClientExtension();
        if (/*$extension == 'doc' || $extension == 'docx' ||*/$extension == 'pdf') {
            $name = "material_" . time();
            $ext_name = "material_" . time() . "." . $extension;
            $unconverted_file = $request->file('material')->storeAs($folder, $ext_name, 'public');
            // $file = $this->convertDocToPDF($unconverted_file, $folder, $name, $extension);
            $inputs['title'] = $title;
            $inputs['material'] = $unconverted_file; //$file;
            $inputs['teacher_id'] = $teacher_id;
            $inputs['school_id'] = $this->getSchool()->id;
            $inputs['subject_teacher_id'] = $subject_teacher_id;
            $material->create($inputs);

            $title = "Study Material Uploaded";
            $action = "Study material titled $title for " . $subject_teacher->subject->name . " (" . $subject_teacher->classTeacher->c_class->name . ") was uploaded";
            $this->auditTrailEvent($title, $action, $subject_teacher->class_teacher_id);

            return response()->json([], 200);
        }
        return response()->json(['message' => 'Only PDF files are needed'], 500);
    }
    private function convertDocToPDF($file, $folder, $name, $extension)
    {
        // return portalPulicPath($file); // public_path($file);
        if ($extension == 'doc' || $extension == 'docx') {
            $word_file = $file;

            $domPdfPath = base_path('vendor/dompdf/dompdf');
            \PhpOffice\PhpWord\Settings::setPdfRendererPath($domPdfPath);
            \PhpOffice\PhpWord\Settings::setPdfRendererName('DomPDF');
            $Content = \PhpOffice\PhpWord\IOFactory::load(portalPulicPath($word_file));
            $PDFWriter = \PhpOffice\PhpWord\IOFactory::createWriter($Content, 'PDF');

            $file = $folder . '/' . $name . ".pdf";
            $PDFWriter->save(portalPulicPath($file));
            Storage::disk('public')->delete($word_file);
        }
        return $file;
        // echo 'File has been successfully converted';
    }
    public function subjectMaterials(SubjectTeacher $subject_teacher)
    {
        $user = $this->getUser();
        if ($user->role === 'student') {
            $materials = Material::with(['subjectTeacher.subject', 'teacher', 'subjectTeacher.classTeacher.c_class'])->where('status', 'active')->where('subject_teacher_id', $subject_teacher->id)->get();
        } else {
            $materials = Material::with(['subjectTeacher.subject', 'teacher', 'subjectTeacher.classTeacher.c_class'])->where('subject_teacher_id', $subject_teacher->id)->get();
        }
        return response()->json(compact('materials'), 200);
    }

    public function changeStatus(Request $request, Material $material)
    {
        $material->status = $request->status;
        $material->save();
        return response()->json([], 204);
    }

    public function destroy($id)
    {
        try {
            $material = Material::findOrFail($id);
            if (Storage::disk('public')->exists($material->material)) {
                Storage::disk('public')->delete($material->material);
            }
            $material->delete();
            return response()->json([], 204);
        } catch (ModelNotFoundException $ex) {
            return redirect()->route('materials.index');
        }
    }

    public function materials(Material $material)
    {

        $materials = Material::join('subject_teachers', 'materials.subject_teacher_id', '=', 'subject_teachers.id')
            ->where('materials.school_id', $this->getSchool()->id)
            ->orderBy('materials.id', 'DESC')
            ->select('materials.id', 'subject_teachers.id as subject_teacher_id', 'title', 'material', 'materials.created_at')
            ->get();


        $materials = $material->teacherMaterialsDetails($materials);

        return $this->render('material::materials.materials', compact('materials'));
    }

    public function readMaterial(Material $material)
    {
        // $folder = 'schools/1561062061/materials/material_1586028505.docx';
        $path = portalPulicPath($material->material);
        return response()->file($path);
    }
}
