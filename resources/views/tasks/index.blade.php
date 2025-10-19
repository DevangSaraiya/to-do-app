@extends('layout')
@section('pageWiseCss')
    <style>
        .highlight {
            background-color: #0093e94d;
            transition: background-color 2s ease-out;
            border-radius: 5px;
            border: 2px solid skyblue;
        }
    </style>
@endsection
@section('content')
    @if (session('success'))
        <div class="alert mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10" />
                <path d="m9 12 2 2 4-4" />
            </svg>
            <h2>Success!</h2>
            <section>{{ session('success') }}</section>
        </div>
    @endif

    @if (session('error'))
        <div class="alert-destructive mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10" />
                <line x1="12" x2="12" y1="8" y2="12" />
                <line x1="12" x2="12.01" y1="16" y2="16" />
            </svg>
            <h2>Something went wrong!</h2>
            <section>{{ session('error') }}</section>
        </div>
    @endif
    <div class="card w-full">
        <section>
            <form class="form grid gap-6" action="{{ route('task.store') }}" method="POST">
                @csrf
                <div class="grid gap-2">
                    <label for="task_description">Description</label>
                    <div class="grid grid-cols-[1fr_180px] gap-2">
                        <input type="text" id="task_description" name="description" placeholder="describe the task..."
                            tabindex="1" autofocus>
                        <button type="submit" class="btn" tabindex="3">Add</button>
                    </div>
                    @error('description')
                        <p class="text-red-500 text-sm">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid gap-2">
                    <label for="task_due_date">Due date</label>
                    <input type="date" id="task_due_date" name="due_date" tabindex="2">
                    @error('due_date')
                        <p class="text-red-500 text-sm">{{ $message }}</p>
                    @enderror
                </div>
            </form>
        </section>

        <hr>

        <section>
            <form class="form flex gap-2 mb-6" action="{{ route('task.index') . '?' . json_encode(request()->all()) }}">
                <label for="filter_status">Filter tasks</label>
                <select id="filter_status" name="status">
                    <option value="" {{ request('status') == '' ? 'selected' : '' }}>All</option>
                    <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
                    <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                </select>
                <button type="submit" class="btn">Filter</button>
            </form>

            <ul class="grid gap-4">
                @forelse ($tasks as $task)
                    <li class="flex items-center gap-4 @if (session('highlight_task') == $task->id) highlight @endif">
                        <div class="flex flex-col gap-1 mr-auto" id="task-box-{{ $task->id }}">

                            <!-- Editable Description -->
                            <div id="task_description_{{ $task->id }}" class="editable-field" data-field="description"
                                data-task-id="{{ $task->id }}">
                                <p class="text-sm font-medium leading-none {{ $task->done ? 'line-through' : '' }}">
                                    {{ $task->description ?? '' }}
                                </p>
                            </div>

                            <!-- Editable Due Date -->
                            <div id="task_due_date_{{ $task->id }}" class="editable-field" data-field="due_date"
                                data-task-id="{{ $task->id }}">
                                <p class="text-sm font-muted leading-none {{ $task->done ? 'line-through' : '' }}" data-date="{{ !empty($task->due_date) ? \Carbon\Carbon::createFromFormat('d-m-Y', $task->due_date)->format('Y-m-d') : '' }}">
                                    {{ $task->due_date ?? '' }}
                                </p>
                            </div>
                        </div>

                        @if (!$task->done)
                            <form class="form" action="{{ route('task.done', $task->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn-sm-outline">Done</button>
                            </form>
                        @endif
                    </li>
                @empty
                    <li class="flex items-center gap-4">
                        <div class="flex flex-col gap-1 mr-auto">
                            <p class="text-sm font-medium leading-none">No Task Found</p>
                        </div>
                    </li>
                @endforelse
            </ul>
        </section>
    </div>
@endsection

@section('pageWiseScript')
    <script>
        setTimeout(() => {
            document.querySelector('.highlight')?.classList.remove('highlight');

            document.querySelectorAll('.alert, .alert-destructive').forEach(el => {
                el.style.transition = 'opacity 0.5s ease-out';
                el.style.opacity = 0;
                setTimeout(() => el.remove(), 500);
            });
        }, 1500);


        // Listen for clicks on description or due date to edit both fields
        $(document).on('click', '.editable-field', function() {
            if ($(this).find('.line-through').length > 0) {
                alert("Task already completed");
                return false;
            }
            const taskId = $(this).data('task-id');
            const descriptionText = $('#task_description_' + taskId + ' p').text().trim();
            const dueDateText = $('#task_due_date_' + taskId + ' p').attr('data-date').trim();

            // Create form elements for both fields
            const descriptionInput = $('<input>')
                .attr('id', 'description')
                .attr('type', 'text')
                .attr('name', 'description')
                .attr('placeholder', 'Describe the task...')
                .val(descriptionText)
                .attr('tabindex', '1')
                .attr('autofocus', true);

            const dueDateInput = $('<input>')
                .attr('id', 'due_date')
                .attr('type', 'date')
                .attr('name', 'due_date')
                .val(dueDateText)
                .attr('tabindex', '2');

            // Create a form containing both fields
            const form = $('<form>')
                .addClass('form grid gap-6')
                .attr('action', 'javascript:;')
                .attr('method', 'POST')
                .append(
                    $('<div>')
                    .addClass('grid gap-2')
                    .append('<label for="task_description">Description</label>')
                    .append(descriptionInput)
                    .append(`<span id="task_description_error_${taskId}" class="text-red-500 text-sm"></span>`)
                )
                .append(
                    $('<div>')
                    .addClass('grid gap-2')
                    .append('<label for="task_due_date">Due Date</label>')
                    .append(dueDateInput)
                    .append(`<span id="task_due_date_error_${taskId}" class="text-red-500 text-sm"></span>`)
                )
                .append(
                    $('<div>')
                    .addClass('grid gap-2')
                    .append('<p class="text-red-500 text-sm error-message"></p>') // Placeholder for error messages
                )
                .append('<button type="submit" class="btn">Update</button>');

            // Add the cancel button
            form.append(
                $('<button>')
                .attr('type', 'button')
                .addClass('btn-sm-outline cancel-edit')
                .text('Cancel')
            );

            // Replace the current task description and due date with the new form
            $('#task-box-' + taskId).html(form);

            // Handle form submission (Save)
            form.submit(function(e) {
                e.preventDefault();
                const newDescription = descriptionInput.val();
                const newDueDate = dueDateInput.val();
                saveTask(taskId, newDescription, newDueDate);
            });

            // Handle cancel button click (Restore previous values)
            $(document).on('click', '.cancel-edit', function() {
                restorePreviousValues(taskId);
            });
        });

        // Save task via AJAX request
        function saveTask(taskId, newDescription, newDueDate) {
            $.ajax({
                url: '/task/' + taskId,
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    _method: 'PATCH',
                    description: newDescription,
                    due_date: newDueDate
                },
                success: function(response) {
                    if (response.success) {
                        $('#task_description_' + taskId).html('<p>' + newDescription + '</p>');
                        $('#task_due_date_' + taskId).html('<p>' + newDueDate + '</p>');
                        restorePreviousValues(taskId);
                    } else {
                        alert('Failed to update task!');
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        showValidationErrors(taskId, errors);
                    } else {
                        alert('Something went wrong!');
                    }

                }
            });
        }

        function showValidationErrors(taskId, errors) {
            clearValidationErrors(taskId);
            $.each(errors, function (i,v) {
                $(`#task_${i}_error_${taskId}`).text(v[0]);
            });
        }

        function clearValidationErrors(taskId) {
            $('#task_description_error_' + taskId).text('');
            $('#task_due_date_error_' + taskId).text('');
        }

        // Restore original task values if user cancels
        function restorePreviousValues(taskId) {
            $.get('/task/' + taskId, function(response) {
                const task = response.task;
                const taskRow = `<div id="task_description_${taskId}" class="editable-field" data-field="description"
                                data-task-id="${taskId}">
                                <p class="text-sm font-medium leading-none">
                                    ${task.description}
                                </p>
                            </div>

                            <!-- Editable Due Date -->
                            <div id="task_due_date_${taskId}" class="editable-field" data-field="due_date"
                                data-task-id="${taskId}">
                                <p class="text-sm font-muted leading-none">
                                    ${task.due_date}
                                </p>
                            </div>`;

                $('#task-box-' + taskId).html(taskRow);
            });
        }
    </script>
@endsection
