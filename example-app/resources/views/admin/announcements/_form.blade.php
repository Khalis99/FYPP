{{-- Expects $announcement and $audienceOptions. The pressed button sets the status. --}}
<x-form.input name="title" label="Title" :value="$announcement->title" required />
<x-form.select name="audience" label="Target audience" :options="$audienceOptions" :value="$announcement->audience" required />
<x-form.textarea name="body" label="Description" :value="$announcement->body" rows="8" required />
@error('status')<p class="form-error">{{ $message }}</p>@enderror

<div class="flex flex-wrap gap-3">
    <button type="submit" name="status" value="published" class="btn btn-primary"><x-icon name="announcements" /> {{ $announcement->isPublished() ? 'Save' : 'Publish now' }}</button>
    @unless ($announcement->isPublished())
        <button type="submit" name="status" value="draft" class="btn btn-secondary">Save as draft</button>
    @else
        <button type="submit" name="status" value="draft" class="btn btn-secondary">Unpublish (save as draft)</button>
    @endunless
    <a href="{{ route('admin.announcements.index') }}" class="btn btn-secondary">Cancel</a>
</div>
