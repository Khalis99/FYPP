{{-- One shared dialog, opened by app.js for any <form data-confirm="..."> --}}
<dialog id="confirm-dialog" class="m-auto w-[min(28rem,calc(100vw-2rem))] rounded-3xl border border-slate-200 p-0 shadow-lift">
    <div class="p-6">
        <div class="flex items-start gap-4">
            <span class="rounded-full bg-red-100 p-2 text-red-600"><x-icon name="warning" class="size-6" /></span>
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Please confirm</h2>
                <p class="mt-1 text-[15px] text-slate-600" data-confirm-message></p>
            </div>
        </div>
    </div>
    <div class="flex justify-end gap-3 border-t border-slate-100 bg-slate-50/70 px-6 py-4">
        <button type="button" class="btn btn-secondary" data-confirm-cancel>Cancel</button>
        <button type="button" class="btn btn-danger" data-confirm-accept>Yes, delete</button>
    </div>
</dialog>
