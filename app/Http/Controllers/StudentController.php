<?php

namespace App\Http\Controllers;

use App\Models\ShaveTokenPurchase;
use App\Models\ShavingQueue;
use App\Models\Student;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;


class StudentController extends Controller
{



    public function index()
    {
        $students = Student::all();

        $studentData = [];

        foreach ($students as $student) {
            $tokenPurchases = ShaveTokenPurchase::where('student_id', $student->id)->get();
            $tokenTypes = $tokenPurchases->pluck('token_type')->unique();
            $numTokens = $tokenPurchases->count();

            $queue_info = ShavingQueue::where('student_id', $student->id)->first();

            if ($queue_info) {
                $queue_number = $queue_info->queue_number;
                $tokenTypeDisplays = [];

                foreach ($tokenTypes as $tokenType) {
                    $tokenQueueInfo = ShavingQueue::where('student_id', $student->id)
                        ->where('type', $tokenType)
                        ->first();

                    if ($tokenQueueInfo) {
                        $tokenTypeDisplays[] = "{$tokenType}: {$tokenQueueInfo->queue_number}";
                    }
                }

            } else {
                $queue_info = ['token_ type_owned' => 'Not in queue'];
            }

            $studentData[] = [
                'name' => $student->name,
                'uid' => $student->uid,
                'num_tokens' => $numTokens,
                'token_types' => $tokenTypes->toArray(),
                'queue_info' => $queue_info,
            ];
        }

        return response()->json($studentData);
    }



    public function addStudent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'uid' => 'required|integer|min:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Memeriksa apakah ada student dengan UID yang sama
        $existingStudent = Student::where('uid', $request->input('uid'))->first();

        if ($existingStudent) {
            return response()->json(['error' => 'UID sudah ada dalam sistem'], 400);
        }

        $santri = new Student();
        $santri->name = $request->input('name');
        $santri->uid = $request->input('uid');
        $santri->save();

        return response()->json(['message' => 'Student berhasil dibuat'], 201);
    }


public function edit(Request $request, $id)
{
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'uid' => 'required|integer|min:25',
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 400);
    }

    $student = Student::where('id', $id)->update([
        'name' => $request->input('name'),
        'uid' => $request->input('uid'),
    ]);

    if (!$student) {
        return response()->json(['message' => 'Student not found'], 404);
    }
}

public function delete(Request $request, $id)
{
    $student = Student::findOrFail($id);
    $student->delete();

    return response()->json(['message' => 'Student deleted successfully']);
}
}
