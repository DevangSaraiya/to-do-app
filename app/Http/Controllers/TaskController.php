<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskRequest;
use App\Models\Task;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{
    /**
     * Display task list with form
     */
    public function index()
    {
        $allTasks = Task::when(request('status') === 'open', fn($query) => $query->where('done', false))
            ->when(
                request('status') === 'overdue',
                fn($query) => $query
                    ->where('done', false)
                    ->whereNotNull('due_date')
                    ->where('due_date', '<', now())
            )
            ->orderByRaw("CASE WHEN due_date IS NULL THEN 1 ELSE 0 END")
            ->orderBy('due_date', 'asc')
            ->orderBy('created_at', 'desc')
            ->orderBy('done', 'asc')
            ->get();
        return view('tasks.index', [
            'tasks' => $allTasks
        ]);
    }

    /**
     * Store new value
     */
    public function store(TaskRequest $request)
    {
        try {
            $task = Task::create($request->only('description', 'due_date'));
            session()->flash('highlight_task', $task->id);
            return redirect()->back()->with('success', 'Task Created Successfully');
        } catch (\Exception $ex) {
            Log::error("Error occur in task creation process: " . $ex->getMessage() . ', In File: ' . $ex->getFile() . ', In Line: ' . $ex->getLine());
            Log::error($ex->getTraceAsString());
            return redirect()->back()->with('error', 'Error occurs in Task Cretation! Please try again later.');
        }
    }
    /**
     * Used for retrive single task latest value
     */
    public function show(Task $task) {
        try {
            if (empty($task)){
                return response()->json([
                    'success' => false,
                    'message' => 'Task nor found'
                ]);
            }
            return response()->json([
                'success' => true,
                'message' => 'Task found',
                'task' => [
                    'description' => $task->description,
                    'due_date' => ($task->due_date) ? $task->due_date : ''
                ]
            ]);
            return response()->json(['success' => true]);
        } catch (\Exception $ex) {
            Log::error("Error occur in task retriving process: " . $ex->getMessage() . ', In File: ' . $ex->getFile() . ', In Line: ' . $ex->getLine());
            Log::error($ex->getTraceAsString());
            return response()->json(['success' => false, 'message' => 'Error in retriving task']);
        }
    }

    /**
     * Used for update single task
     */
    public function update(TaskRequest $request, Task $task) {
        try {
            if (empty($task)){
                return response()->json([
                    'success' => false,
                    'message' => 'Task nor found'
                ]);
            }
            $task->update($request->only('description', 'due_date'));
            return response()->json(['success' => true, 'message' => 'Task Updated Successfully']);
        } catch (\Exception $ex) {
            Log::error("Error occur in task updating process: " . $ex->getMessage() . ', In File: ' . $ex->getFile() . ', In Line: ' . $ex->getLine());
            Log::error($ex->getTraceAsString());
            return response()->json(['success' => false, 'message' => 'Error updating task']);
        }
    }

    /**
     * completeTask function used for completing task
     */
    public function completeTask(Task $task)
    {
        try {
            if (empty($task)) {
                return redirect()->back()->with('error', 'Task not found');
            }
            $task->done = true;
            $task->save();
            return redirect()->back()->with('success', 'Task completed successfully');
        } catch (\Exception $ex) {
            Log::error("Error occur in task creation process: " . $ex->getMessage() . ', In File: ' . $ex->getFile() . ', In Line: ' . $ex->getLine());
            Log::error($ex->getTraceAsString());
            return redirect()->back()->with('error', 'Error occurs while completing task! Please try again later.');
        }
    }
}
