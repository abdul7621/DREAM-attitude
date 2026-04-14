@if (!empty($product->meta['ingredients']) || !empty($product->meta['how_to_use']))
    <div style="margin-top:24px;padding-top:20px;border-top:1px solid var(--color-border);">
        <h6 style="font-weight:600;font-size:16px;margin-bottom:16px;color:var(--color-text-primary);"><i class="bi bi-list-ul" style="color:var(--color-gold);margin-right:8px;"></i>Details & Specifications</h6>
        @if (!empty($product->meta['ingredients']))
            <div style="border-bottom:1px solid var(--color-border);margin-bottom:8px;">
                <button type="button" onclick="var p=this.nextElementSibling; if(p.style.display==='none'){p.style.display='block';this.querySelector('.acc-icon').className='bi bi-dash acc-icon';}else{p.style.display='none';this.querySelector('.acc-icon').className='bi bi-plus acc-icon';}"
                    style="width:100%;background:none;border:none;color:var(--color-text-primary);font-weight:600;font-size:14px;text-align:left;padding:12px 0;cursor:pointer;display:flex;justify-content:space-between;align-items:center;">
                    Ingredients
                    <i class="bi bi-plus acc-icon" style="color:var(--color-gold);"></i>
                </button>
                <div style="display:none;padding:0 0 16px;color:var(--color-text-secondary);font-size:13px;line-height:1.6;">
                    {!! nl2br(e($product->meta['ingredients'])) !!}
                </div>
            </div>
        @endif
        @if (!empty($product->meta['how_to_use']))
            <div style="border-bottom:1px solid var(--color-border);margin-bottom:8px;">
                <button type="button" onclick="var p=this.nextElementSibling; if(p.style.display==='none'){p.style.display='block';this.querySelector('.acc-icon').className='bi bi-dash acc-icon';}else{p.style.display='none';this.querySelector('.acc-icon').className='bi bi-plus acc-icon';}"
                    style="width:100%;background:none;border:none;color:var(--color-text-primary);font-weight:600;font-size:14px;text-align:left;padding:12px 0;cursor:pointer;display:flex;justify-content:space-between;align-items:center;">
                    How to Use
                    <i class="bi bi-plus acc-icon" style="color:var(--color-gold);"></i>
                </button>
                <div style="display:none;padding:0 0 16px;color:var(--color-text-secondary);font-size:13px;line-height:1.6;">
                    {!! nl2br(e($product->meta['how_to_use'])) !!}
                </div>
            </div>
        @endif
    </div>
@endif

