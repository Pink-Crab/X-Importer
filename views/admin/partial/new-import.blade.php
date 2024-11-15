<div id="new-import" >
    <h2>@__('Import new data')</h2>

    <input type="hidden" id="pc_x_new_nonce" value="{{ $data['nonce'] }}" />
    <input type="file" id="pc_x_new_file" name="pc_x_new_file" accept=".json" />

    <select id="pc_x_new_duplicate">
        <option value="update">@__('Update existing tweet')</option>
        <option value="skip">@__('Skip existing tweets')</option>
    </select>

    <select id="pc_x_new_format">
        @foreach ($data['formats'] as $key => $format)
            <option value="{{ $key }}">{{ $format }}</option>
        @endforeach
    </select>

    <button id="pc_x_new_submit">@__('Import')</button>
</div>
