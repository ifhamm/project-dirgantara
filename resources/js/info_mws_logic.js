// ═══════════════════════════════════════════════════════════════
// UTILITIES
// ═══════════════════════════════════════════════════════════════

/**
 * Kirim request JSON ke server
 * @param {string} url
 * @param {string} method  GET | POST | PUT | DELETE
 * @param {object|null} body
 * @returns {Promise<object>}
 */
const getMwsConfig = () => {
    const el = document.getElementById("mws-app");
    if (!el || !el.dataset.mws) {
        console.error(
            '❌ data-mws tidak ditemukan. Pastikan ada <div id="mws-app" data-mws="...">',
        );
        return {};
    }
    try {
        return JSON.parse(el.dataset.mws);
    } catch (e) {
        console.error("❌ Gagal parse data-mws:", e);
        return {};
    }
};

// Shorthand untuk dipakai di seluruh file
const cfg = () => getMwsConfig();
const partId = () => cfg().partId;
const csrfToken = () => cfg().csrfToken;
const isMwsLocked = () => cfg().isLocked;

async function apiFetch(url, method = "GET", body = null) {
    const opts = {
        method,
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": csrfToken(),
        },
    };
    if (body) opts.body = JSON.stringify(body);

    const res = await fetch(url, opts);

    if (!res.ok) {
        const err = await res.json().catch(() => ({}));
        throw new Error(err.message || `HTTP ${res.status}`);
    }
    return res.json();
}

/**
 * Tampilkan toast notifikasi
 * @param {string} message
 * @param {'success'|'error'|'info'} type
 */
function showToast(message, type = "success") {
    const el = document.getElementById("toast-notification");
    const msg = document.getElementById("toast-message");
    const icon = document.getElementById("toast-icon");
    if (!el) return;

    const iconMap = {
        success: "fas fa-check-circle text-xl mt-0.5",
        error: "fas fa-times-circle text-xl mt-0.5",
        info: "fas fa-info-circle text-xl mt-0.5",
    };

    el.className = type;
    el.style.display = "block";
    msg.textContent = message;
    icon.className = iconMap[type] || iconMap.info;

    clearTimeout(el._timer);
    el._timer = setTimeout(() => dismissToast(), 4000);
}

function dismissToast() {
    const el = document.getElementById("toast-notification");
    if (el) el.style.display = "none";
}

function dismissStrippingNotification() {
    const el = document.getElementById("stripping-notification");
    if (el) el.style.display = "none";
}

// ═══════════════════════════════════════════════════════════════
// SECTION TOGGLE
// ═══════════════════════════════════════════════════════════════

function toggleSection(id) {
    const el = document.getElementById(id);
    if (el) el.classList.toggle("hidden");
}

// ═══════════════════════════════════════════════════════════════
// MWS INFO — EDIT / SAVE
// ═══════════════════════════════════════════════════════════════

function toggleEditMwsInfo(show) {
    document.getElementById("mws-info-view")?.classList.toggle("hidden", show);
    document.getElementById("mws-info-edit")?.classList.toggle("hidden", !show);
}

async function saveMwsInfo(e) {
    e.preventDefault();
    const form = e.target;
    const data = Object.fromEntries(new FormData(form));

    try {
        await apiFetch(`/mws/${partId()}`, "PUT", data);
        showToast("MWS Info berhasil disimpan!");
        location.reload();
    } catch (err) {
        showToast(err.message || "Gagal menyimpan.", "error");
    }
}

// ═══════════════════════════════════════════════════════════════
// PLAN EDIT (inline per kolom MAN / HOURS)
// ═══════════════════════════════════════════════════════════════

function togglePlanEdit(stepNo, field, show) {
    const viewId = `plan-${field}-view-${stepNo}`;
    const editId = `plan-${field}-edit-${stepNo}`;
    const view   = document.getElementById(viewId);
    const edit   = document.getElementById(editId);

    if (!view || !edit) {
        console.error(`❌ Element tidak ditemukan: ${viewId} atau ${editId}`);
        return;
    }

    if (show) {
        view.style.display = 'none';
        edit.style.display = 'block';
        edit.classList.remove('d-none');
    } else {
        view.style.display  = '';
        view.classList.remove('d-none');
        edit.style.display  = 'none';
    }
}

async function savePlan(mwsPartId, stepNo, field) {
    const input = document.getElementById(`plan-${field}-input-${stepNo}`);
    if (!input) return;

    if (field === "hours") {
        const val = input.value.trim();
        if (!/^\d+:\d{2}$/.test(val)) {
            showToast("Format durasi harus HH:MM (contoh: 01:30)", "error");
            return;
        }
    }

    try {
        await apiFetch(`/mws/step/${stepNo}/update`, "POST", {
            field: field === "man" ? "plan_man" : "plan_hours",
            value: input.value,
            mws_part_id: mwsPartId,
        });

        // Update tampilan teks
        const textEl = document.getElementById(`plan-${field}-text-${stepNo}`);
        if (textEl) textEl.textContent = input.value || "N/A";

        togglePlanEdit(stepNo, field, false);
        showToast("Plan berhasil disimpan!");
    } catch (err) {
        showToast(err.message || "Gagal menyimpan plan.", "error");
    }
}

// ═══════════════════════════════════════════════════════════════
// STEP DESCRIPTION — EDIT
// ═══════════════════════════════════════════════════════════════

async function editStepDescription(mwsPartId, stepNo) {
    const el = document.getElementById(`step-desc-${stepNo}`);
    const current = el ? el.textContent.trim() : "";
    const newDesc = prompt("Edit deskripsi step:", current);
    if (newDesc === null || newDesc === current) return;

    try {
        await apiFetch(`/mws/step/${stepNo}/update`, "POST", {
            field: "description",
            value: newDesc,
            mws_part_id: mwsPartId,
        });
        if (el) el.textContent = newDesc;
        showToast("Deskripsi diperbarui!");
    } catch (err) {
        showToast(err.message || "Gagal mengupdate deskripsi.", "error");
    }
}

// ═══════════════════════════════════════════════════════════════
// STEP — ADD / INSERT / DELETE
// ═══════════════════════════════════════════════════════════════

async function addFirstStep(mwsPartId) {
    const desc = prompt("Deskripsi step baru:");
    if (!desc) return;

    try {
        await apiFetch(`/mws/${mwsPartId}/steps`, "POST", {
            description: desc,
        });
        showToast("Step berhasil ditambahkan!");
        location.reload();
    } catch (err) {
        showToast(err.message || "Gagal menambah step.", "error");
    }
}

async function insertStepAfter(mwsPartId, stepNo) {
    const desc = prompt(
        "Deskripsi step baru (akan disisipkan setelah step ini):",
    );
    if (!desc) return;

    try {
        await apiFetch(
            `/mws/${mwsPartId}/steps/${stepNo}/insert-after`,
            "POST",
            {
                description: desc,
            },
        );
        showToast("Step berhasil disisipkan!");
        location.reload();
    } catch (err) {
        showToast(err.message || "Gagal menyisipkan step.", "error");
    }
}

async function deleteStep(mwsPartId, stepNo) {
    if (!confirm(`Hapus step no ${stepNo}?`)) return;

    try {
        await apiFetch(`/mws/${mwsPartId}/steps/${stepNo}`, "DELETE");
        const row = document.getElementById(`step-row-${stepNo}`);
        if (row) row.remove();
        showToast("Step dihapus.");
        location.reload();
    } catch (err) {
        showToast(err.message || "Gagal menghapus step.", "error");
    }
}

// ── Bulk Delete ──────────────────────────────────────────────

function updateSmartDeleteBtn() {
    const checked = document.querySelectorAll(".step-checkbox:checked").length;
    const btn = document.getElementById("smart-delete-btn");
    if (btn) btn.classList.toggle("hidden", checked === 0);
}

async function handleSmartDelete(mwsPartId) {
    const checked = [...document.querySelectorAll(".step-checkbox:checked")];
    if (!checked.length) return;

    const stepNos = checked.map((cb) => cb.dataset.stepNo);
    if (!confirm(`Hapus ${stepNos.length} step terpilih?`)) return;

    try {
        await apiFetch(`/mws/${mwsPartId}/steps/bulk-delete`, "DELETE", {
            step_nos: stepNos,
        });
        showToast(`${stepNos.length} step dihapus.`);
        location.reload();
    } catch (err) {
        showToast(err.message || "Gagal menghapus.", "error");
    }
}

// ═══════════════════════════════════════════════════════════════
// DETAIL (catatan per step)
// ═══════════════════════════════════════════════════════════════

async function addDetail(mwsPartId, stepNo) {
    const input = document.getElementById(`new-detail-input-${stepNo}`);
    if (!input) return;
    const text = input.value.trim();
    if (!text) return showToast("Catatan tidak boleh kosong.", "error");

    try {
        await apiFetch(`/mws/${mwsPartId}/steps/${stepNo}/details`, "POST", {
            detail: text,
        });
        input.value = "";
        showToast("Catatan ditambahkan!");
        location.reload();
    } catch (err) {
        showToast(err.message || "Gagal menambah catatan.", "error");
    }
}

async function editDetail(mwsPartId, stepNo, detailIndex) {
    const el = document.getElementById(`detail-text-${stepNo}-${detailIndex}`);
    const current = el ? el.textContent.trim() : "";
    const newText = prompt("Edit catatan:", current);
    if (newText === null || newText === current) return;

    try {
        await apiFetch(
            `/mws/${mwsPartId}/steps/${stepNo}/details/${detailIndex}`,
            "PUT",
            { detail: newText },
        );
        if (el) el.textContent = newText;
        showToast("Catatan diperbarui!");
    } catch (err) {
        showToast(err.message || "Gagal mengupdate catatan.", "error");
    }
}

async function deleteDetail(mwsPartId, stepNo, detailIndex) {
    if (!confirm("Hapus catatan ini?")) return;

    try {
        await apiFetch(
            `/mws/${mwsPartId}/steps/${stepNo}/details/${detailIndex}`,
            "DELETE",
        );
        document
            .getElementById(`detail-item-${stepNo}-${detailIndex}`)
            ?.remove();
        showToast("Catatan dihapus.");
    } catch (err) {
        showToast(err.message || "Gagal menghapus catatan.", "error");
    }
}

// ═══════════════════════════════════════════════════════════════
// CAUTION & NOTE per STEP
// ═══════════════════════════════════════════════════════════════

function toggleCautionEdit(stepNo, show) {
    document
        .getElementById(`caution-edit-${stepNo}`)
        ?.classList.toggle("hidden", !show);
}

function toggleNoteEdit(stepNo, show) {
    document
        .getElementById(`note-edit-${stepNo}`)
        ?.classList.toggle("hidden", !show);
}

async function saveCaution(mwsPartId, stepNo) {
    const caution =
        document.getElementById(`caution-input-${stepNo}`)?.value ?? "";
    const note = document.getElementById(`note-input-${stepNo}`)?.value ?? "";

    try {
        await apiFetch(`/mws/${mwsPartId}/steps/${stepNo}/caution`, "PUT", {
            caution,
            note,
        });
        showToast("Caution disimpan!");
        location.reload();
    } catch (err) {
        showToast(err.message || "Gagal menyimpan caution.", "error");
    }
}

async function saveNote(mwsPartId, stepNo) {
    // Endpoint sama, hanya nilai note yang berubah
    await saveCaution(mwsPartId, stepNo);
}

// ═══════════════════════════════════════════════════════════════
// SUB-STEPS
// ═══════════════════════════════════════════════════════════════

async function addSubStep(mwsPartId, stepNo) {
    const input = document.getElementById(`new-substep-input-${stepNo}`);
    if (!input) return;
    const desc = input.value.trim();
    if (!desc) return showToast("Deskripsi sub-step wajib diisi.", "error");

    try {
        await apiFetch(`/mws/${mwsPartId}/steps/${stepNo}/substeps`, "POST", {
            description: desc,
        });
        input.value = "";
        showToast("Sub-step ditambahkan!");
        location.reload();
    } catch (err) {
        showToast(err.message || "Gagal menambah sub-step.", "error");
    }
}

async function editSubStep(mwsPartId, stepNo, subStepId) {
    const el = document.getElementById(`substep-text-${subStepId}`);
    const current = el ? el.textContent.trim() : "";
    const newDesc = prompt("Edit sub-step:", current);
    if (newDesc === null || newDesc === current) return;

    try {
        await apiFetch(
            `/mws/${mwsPartId}/steps/${stepNo}/substeps/${subStepId}`,
            "PUT",
            { description: newDesc },
        );
        if (el) el.textContent = newDesc;
        showToast("Sub-step diperbarui!");
    } catch (err) {
        showToast(err.message || "Gagal mengupdate sub-step.", "error");
    }
}

async function deleteSubStep(mwsPartId, stepNo, subStepId) {
    if (!confirm("Hapus sub-step ini?")) return;

    try {
        await apiFetch(
            `/mws/${mwsPartId}/steps/${stepNo}/substeps/${subStepId}`,
            "DELETE",
        );
        document.getElementById(`substep-item-${subStepId}`)?.remove();
        showToast("Sub-step dihapus.");
        // Reload agar label a, b, c... di-reorder dari server
        location.reload();
    } catch (err) {
        showToast(err.message || "Gagal menghapus sub-step.", "error");
    }
}

// ═══════════════════════════════════════════════════════════════
// CONSUMABLES
// ═══════════════════════════════════════════════════════════════

function toggleConsumableAdd(show) {
    const triggerRow = document.getElementById("consumable-add-trigger");
    const inputRow = document.getElementById("consumable-add-row");
    if (!triggerRow || !inputRow) return;

    if (show) {
        triggerRow.classList.add("d-none");
        inputRow.classList.remove("d-none");
        document.getElementById("new-cons-name")?.focus();
        return;
    }

    inputRow.classList.add("d-none");
    triggerRow.classList.remove("d-none");

    const nameInput = document.getElementById("new-cons-name");
    const identInput = document.getElementById("new-cons-ident");
    const qtyInput = document.getElementById("new-cons-qty");
    if (nameInput) nameInput.value = "";
    if (identInput) identInput.value = "";
    if (qtyInput) qtyInput.value = "";
}

async function addConsumable(mwsPartId) {
    const name = document.getElementById("new-cons-name")?.value.trim();
    const ident = document.getElementById("new-cons-ident")?.value.trim();
    const qty = document.getElementById("new-cons-qty")?.value.trim() || "AR";

    if (!name) return showToast("Nama consumable wajib diisi.", "error");

    try {
        await apiFetch(`/mws/${mwsPartId}/consumables`, "POST", {
            name,
            identification: ident,
            quantity: qty,
        });
        showToast("Consumable ditambahkan!");
        location.reload();
    } catch (err) {
        showToast(err.message || "Gagal menambahkan consumable.", "error");
    }
}

function setConsumableActionButtons(mwsPartId, consumableId, isEditing = false) {
    const actionCell = document.getElementById(`consumable-actions-${consumableId}`);
    if (!actionCell) return;

    if (isEditing) {
        actionCell.innerHTML = `
            <button type="button" class="btn btn-success btn-sm me-1" onclick="saveConsumable('${mwsPartId}', ${consumableId})">Save</button>
            <button type="button" class="btn btn-secondary btn-sm" onclick="cancelConsumableEdit('${mwsPartId}', ${consumableId})">Cancel</button>
        `;
        return;
    }

    actionCell.innerHTML = `
        <button type="button" class="btn btn-outline-primary btn-sm me-1" onclick="editConsumable('${mwsPartId}', ${consumableId})">Edit</button>
        <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteConsumable('${mwsPartId}', ${consumableId})">Hapus</button>
    `;
}

function editConsumable(mwsPartId, consumableId) {
    const row = document.getElementById(`consumable-row-${consumableId}`);
    if (!row) return;

    const nameEl = document.getElementById(`cons-name-${consumableId}`);
    const identEl = document.getElementById(`cons-ident-${consumableId}`);
    const qtyEl = document.getElementById(`cons-qty-${consumableId}`);
    if (!nameEl || !identEl || !qtyEl) return;

    row.dataset.originalName = nameEl.textContent.trim();
    row.dataset.originalIdent = identEl.textContent.trim();
    row.dataset.originalQty = qtyEl.textContent.trim();

    const fields = [
        { key: "name", el: nameEl },
        { key: "ident", el: identEl },
        { key: "qty", el: qtyEl },
    ];

    fields.forEach(({ key, el }) => {
        const input = document.createElement("input");
        input.type = "text";
        input.value = el.textContent.trim() === "-" ? "" : el.textContent.trim();
        input.id = `cons-${key}-input-${consumableId}`;
        input.className = "form-control form-control-sm";
        el.replaceWith(input);
    });

    setConsumableActionButtons(mwsPartId, consumableId, true);
}

function cancelConsumableEdit(mwsPartId, consumableId) {
    const row = document.getElementById(`consumable-row-${consumableId}`);
    if (!row) return;

    const originalName = row.dataset.originalName || "";
    const originalIdent = row.dataset.originalIdent || "-";
    const originalQty = row.dataset.originalQty || "AR";

    const nameInput = document.getElementById(`cons-name-input-${consumableId}`);
    const identInput = document.getElementById(`cons-ident-input-${consumableId}`);
    const qtyInput = document.getElementById(`cons-qty-input-${consumableId}`);

    if (nameInput) {
        const span = document.createElement("span");
        span.id = `cons-name-${consumableId}`;
        span.textContent = originalName;
        nameInput.replaceWith(span);
    }

    if (identInput) {
        const span = document.createElement("span");
        span.id = `cons-ident-${consumableId}`;
        span.textContent = originalIdent || "-";
        identInput.replaceWith(span);
    }

    if (qtyInput) {
        const span = document.createElement("span");
        span.id = `cons-qty-${consumableId}`;
        span.textContent = originalQty || "AR";
        qtyInput.replaceWith(span);
    }

    setConsumableActionButtons(mwsPartId, consumableId, false);
}

async function saveConsumable(mwsPartId, consumableId) {
    const name = document
        .getElementById(`cons-name-input-${consumableId}`)
        ?.value.trim();
    const ident = document
        .getElementById(`cons-ident-input-${consumableId}`)
        ?.value.trim();
    const qty = document
        .getElementById(`cons-qty-input-${consumableId}`)
        ?.value.trim();

    if (!name) return showToast("Nama consumable wajib diisi.", "error");

    try {
        await apiFetch(`/mws/${mwsPartId}/consumables/${consumableId}`, "PUT", {
            name,
            identification: ident,
            quantity: qty || "AR",
        });

        const nameInput = document.getElementById(`cons-name-input-${consumableId}`);
        const identInput = document.getElementById(`cons-ident-input-${consumableId}`);
        const qtyInput = document.getElementById(`cons-qty-input-${consumableId}`);

        if (nameInput) {
            const span = document.createElement("span");
            span.id = `cons-name-${consumableId}`;
            span.textContent = name;
            nameInput.replaceWith(span);
        }

        if (identInput) {
            const span = document.createElement("span");
            span.id = `cons-ident-${consumableId}`;
            span.textContent = ident || "-";
            identInput.replaceWith(span);
        }

        if (qtyInput) {
            const span = document.createElement("span");
            span.id = `cons-qty-${consumableId}`;
            span.textContent = qty || "AR";
            qtyInput.replaceWith(span);
        }

        const row = document.getElementById(`consumable-row-${consumableId}`);
        if (row) {
            row.dataset.originalName = name;
            row.dataset.originalIdent = ident || "-";
            row.dataset.originalQty = qty || "AR";
        }

        setConsumableActionButtons(mwsPartId, consumableId, false);
        showToast("Consumable disimpan!");
    } catch (err) {
        showToast(err.message || "Gagal menyimpan consumable.", "error");
    }
}

async function deleteConsumable(mwsPartId, consumableId) {
    if (!confirm("Hapus consumable ini?")) return;

    try {
        await apiFetch(
            `/mws/${mwsPartId}/consumables/${consumableId}`,
            "DELETE",
        );
        document.getElementById(`consumable-row-${consumableId}`)?.remove();
        showToast("Consumable dihapus.");
    } catch (err) {
        showToast(err.message || "Gagal menghapus consumable.", "error");
    }
}

// ═══════════════════════════════════════════════════════════════
// MEKANIK — SIGN ON / ASSIGN / REMOVE
// ═══════════════════════════════════════════════════════════════

async function addMeToStep(mwsPartId, stepNo) {
    try {
        await apiFetch(`/mws/${mwsPartId}/steps/${stepNo}/sign-on`, "POST");
        showToast("Berhasil sign on ke step ini!");
        location.reload();
    } catch (err) {
        showToast(err.message || "Gagal sign on.", "error");
    }
}

async function assignMechanicToStep(mwsPartId, stepNo) {
    const select = document.getElementById(`assign-mechanic-select-${stepNo}`);
    const nik = select?.value;
    if (!nik) return showToast("Pilih mekanik terlebih dahulu.", "error");

    try {
        await apiFetch(
            `/mws/${mwsPartId}/steps/${stepNo}/assign-mechanic`,
            "POST",
            {
                nik,
            },
        );
        showToast("Mekanik berhasil di-assign!");
        location.reload();
    } catch (err) {
        showToast(err.message || "Gagal assign mekanik.", "error");
    }
}

async function removeMechanicFromStep(mwsPartId, stepNo, nik) {
    if (!confirm("Hapus mekanik dari step ini?")) return;

    try {
        await apiFetch(
            `/mws/${mwsPartId}/steps/${stepNo}/remove-mechanic`,
            "DELETE",
            { nik },
        );
        showToast("Mekanik dihapus dari step.");
        location.reload();
    } catch (err) {
        showToast(err.message || "Gagal menghapus mekanik.", "error");
    }
}

// ═══════════════════════════════════════════════════════════════
// TIMER — START / STOP
// ═══════════════════════════════════════════════════════════════

async function startTimer(mwsPartId, stepNo) {
    try {
        await apiFetch(`/mws/${mwsPartId}/steps/${stepNo}/timer/start`, "POST");
        showToast("Timer dimulai.");
        location.reload();
    } catch (err) {
        showToast(err.message || "Gagal memulai timer.", "error");
    }
}

async function stopTimer(mwsPartId, stepNo) {
    try {
        await apiFetch(`/mws/${mwsPartId}/steps/${stepNo}/timer/stop`, "POST");
        showToast("Timer dihentikan.");
        location.reload();
    } catch (err) {
        showToast(err.message || "Gagal menghentikan timer.", "error");
    }
}

/**
 * Jalankan live counter untuk timer yang sedang berjalan saat halaman dimuat.
 * Dipanggil otomatis saat DOMContentLoaded.
 */
function startLiveTimer(el) {
    const startTime = new Date(el.dataset.startTime);
    const initialStr = el.dataset.initialHours || "00:00";
    const parts = initialStr.split(":").map(Number);
    const initialSecs = (parts[0] * 3600) + (parts[1] * 60);

    const tick = () => {
        const elapsed = Math.floor((Date.now() - startTime) / 1000) + initialSecs;
        const h = String(Math.floor(elapsed / 3600)).padStart(2, "0");
        const m = String(Math.floor((elapsed % 3600) / 60)).padStart(2, "0");
        el.textContent = `${h}:${m}`;
    };

    tick();
    setInterval(tick, 1000);
}


// ═══════════════════════════════════════════════════════════════
// APPROVE / UNAPPROVE (TECH)
// ═══════════════════════════════════════════════════════════════

async function approveStep(mwsPartId, stepNo) {
    try {
        await apiFetch(`/mws/${mwsPartId}/steps/${stepNo}/approve`, "POST");
        showToast("Step di-approve!");
        location.reload();
    } catch (err) {
        showToast(err.message || "Gagal approve.", "error");
    }
}

async function cancelApproval(mwsPartId, stepNo) {
    if (!confirm("Batalkan approval step ini?")) return;

    try {
        await apiFetch(`/mws/${mwsPartId}/steps/${stepNo}/unapprove`, "POST");
        showToast("Approval dibatalkan.");
        location.reload();
    } catch (err) {
        showToast(err.message || "Gagal membatalkan approval.", "error");
    }
}

// ═══════════════════════════════════════════════════════════════
// FINISH / UNAPPROVE (INSP / QUALITY)
// ═══════════════════════════════════════════════════════════════

async function finishStep(mwsPartId, stepNo) {
    try {
        await apiFetch(`/mws/${mwsPartId}/steps/${stepNo}/finish`, "POST");
        showToast("Step diselesaikan!");
        location.reload();
    } catch (err) {
        showToast(err.message || "Gagal menyelesaikan step.", "error");
    }
}

async function cancelFinishStep(mwsPartId, stepNo) {
    if (!confirm("Batalkan penyelesaian step ini?")) return;

    try {
        await apiFetch(`/mws/${mwsPartId}/steps/${stepNo}/unfinish`, "POST");
        showToast("Penyelesaian dibatalkan.");
        location.reload();
    } catch (err) {
        showToast(err.message || "Gagal membatalkan penyelesaian.", "error");
    }
}

// ── Final Inspection ─────────────────────────────────────────

function enableFinalApprove(stepNo) {
    const sel = document.getElementById(`status-s-us-select-${stepNo}`);
    const btn = document.getElementById(`final-approve-btn-${stepNo}`);
    if (!sel || !btn) return;

    if (sel.value) {
        btn.disabled = false;
        btn.classList.remove("opacity-50", "cursor-not-allowed");
    }
}

async function finishFinalInspection(mwsPartId, stepNo) {
    const sel = document.getElementById(`status-s-us-select-${stepNo}`);
    const status = sel?.value;
    if (!status) return showToast("Pilih status terlebih dahulu.", "error");

    try {
        await apiFetch(
            `/mws/${mwsPartId}/steps/${stepNo}/finish-final`,
            "POST",
            {
                status_s_us: status,
            },
        );
        showToast("Final inspection selesai!");
        location.reload();
    } catch (err) {
        showToast(err.message || "Gagal final inspection.", "error");
    }
}

// ═══════════════════════════════════════════════════════════════
// SIGN DOCUMENT (Prepared / Approved / Verified)
// ═══════════════════════════════════════════════════════════════

async function signDocument(mwsPartId, type) {
    const labels = {
        prepared: "Prepared By",
        approved: "Approved By",
        verified: "Verified By",
    };
    if (!confirm(`Anda yakin ingin sign sebagai ${labels[type]}?`)) return;

    try {
        await apiFetch(`/mws/${mwsPartId}/sign`, "POST", { type });
        showToast(`${labels[type]} berhasil di-sign!`);
        location.reload();
    } catch (err) {
        showToast(err.message || "Gagal sign.", "error");
    }
}

async function cancelSignature(mwsPartId, type, confirmMsg) {
    if (!confirm(confirmMsg)) return;

    try {
        await apiFetch(`/mws/${mwsPartId}/cancel-sign`, "POST", { type });
        showToast("Signature dibatalkan.");
        location.reload();
    } catch (err) {
        showToast(err.message || "Gagal membatalkan signature.", "error");
    }
}

// ═══════════════════════════════════════════════════════════════
// ATTACHMENT — MWS LEVEL
// ═══════════════════════════════════════════════════════════════

function updateMwsFileName(input) {
    const display = document.getElementById("mws-file-name-display");
    if (!display) return;
    display.textContent =
        input.files.length > 0
            ? [...input.files].map((f) => f.name).join(", ")
            : "Pilih file lampiran...";
}

async function uploadMwsAttachment(mwsPartId) {
    const input = document.getElementById("mws-attachment-input");
    if (!input?.files.length)
        return showToast("Pilih file terlebih dahulu.", "error");

    const formData = new FormData();
    [...input.files].forEach((f) => formData.append("files[]", f));

    try {
        const res = await fetch(`/mws/${mwsPartId}/attachments`, {
            method: "POST",
            headers: { "X-CSRF-TOKEN": csrfToken() },
            body: formData,
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || "Upload gagal.");
        showToast("File berhasil diupload!");
        location.reload();
    } catch (err) {
        showToast(err.message || "Gagal upload.", "error");
    }
}

async function deleteMwsAttachment(mwsPartId, publicId) {
    if (!confirm("Hapus lampiran ini?")) return;

    try {
        await apiFetch(`/mws/${mwsPartId}/attachments/${publicId}`, "DELETE");
        showToast("Lampiran dihapus.");
        location.reload();
    } catch (err) {
        showToast(err.message || "Gagal menghapus lampiran.", "error");
    }
}

// ── Attachment per Step ──────────────────────────────────────

function updateFileName(input, stepNo) {
    const display = document.getElementById(`file-name-display-${stepNo}`);
    if (!display) return;
    display.textContent =
        input.files.length > 0
            ? [...input.files].map((f) => f.name).join(", ")
            : "Pilih file...";
}

async function uploadStepAttachment(mwsPartId, stepNo) {
    const input = document.getElementById(`step-attachment-input-${stepNo}`);
    if (!input?.files.length)
        return showToast("Pilih file terlebih dahulu.", "error");

    const formData = new FormData();
    [...input.files].forEach((f) => formData.append("files[]", f));

    try {
        const res = await fetch(
            `/mws/${mwsPartId}/steps/${stepNo}/attachments`,
            {
                method: "POST",
                headers: { "X-CSRF-TOKEN": csrfToken() },
                body: formData,
            },
        );
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || "Upload gagal.");
        showToast("File berhasil diunggah!");
        location.reload();
    } catch (err) {
        showToast(err.message || "Gagal unggah.", "error");
    }
}

async function deleteStepAttachment(mwsPartId, stepNo, publicId) {
    if (!confirm("Hapus lampiran ini?")) return;

    try {
        await apiFetch(
            `/mws/${mwsPartId}/steps/${stepNo}/attachments/${publicId}`,
            "DELETE",
        );
        showToast("Lampiran dihapus.");
        location.reload();
    } catch (err) {
        showToast(err.message || "Gagal menghapus lampiran.", "error");
    }
}

// ═══════════════════════════════════════════════════════════════
// DUPLICATE MWS
// ═══════════════════════════════════════════════════════════════

async function confirmDuplicateMws(mwsPartId) {
    if (!confirm("Yakin ingin menduplikasi MWS ini?")) return;

    try {
        const data = await apiFetch(`/mws/${mwsPartId}/duplicate`, "POST");
        if (data.redirect) {
            window.location.href = data.redirect;
        } else {
            showToast(data.message || "MWS berhasil diduplikasi!");
            location.reload();
        }
    } catch (err) {
        showToast(err.message || "Gagal menduplikasi.", "error");
    }
}

// ═══════════════════════════════════════════════════════════════
// SELECT ALL CHECKBOXES
// ═══════════════════════════════════════════════════════════════

function initSelectAll() {
    const selectAll = document.getElementById("select-all-steps");
    if (!selectAll) return;

    selectAll.addEventListener("change", () => {
        document
            .querySelectorAll(".step-checkbox")
            .forEach((cb) => (cb.checked = selectAll.checked));
        updateSmartDeleteBtn();
    });

    document.querySelectorAll(".step-checkbox").forEach((cb) => {
        cb.addEventListener("change", updateSmartDeleteBtn);
    });
}

// ═══════════════════════════════════════════════════════════════
// DOMContentLoaded — INISIALISASI
// ═══════════════════════════════════════════════════════════════

document.addEventListener("DOMContentLoaded", function () {
    // Checkbox bulk select
    initSelectAll();

    // Live timer untuk step yang sedang berjalan
    document.querySelectorAll("[data-start-time]").forEach((el) => {
        startLiveTimer(el);
    });

    // SortableJS Drag-and-Drop untuk MWS Steps
    const stepsTbody = document.getElementById("steps-tbody");
    if (stepsTbody && typeof Sortable !== "undefined") {
        const hasDragHandles = stepsTbody.querySelector(".drag-handle");
        if (hasDragHandles) {
            new Sortable(stepsTbody, {
                handle: ".drag-handle",
                animation: 150,
                onEnd: async function () {
                    const rows = stepsTbody.querySelectorAll(".step-row");
                    const stepIds = Array.from(rows).map(row => Number(row.dataset.id));
                    
                    try {
                        await apiFetch(`/mws/${partId()}/steps/reorder`, "POST", {
                            step_ids: stepIds
                        });
                        showToast("Urutan step berhasil diperbarui!");
                        location.reload();
                    } catch (err) {
                        showToast(err.message || "Gagal mengurutkan step.", "error");
                    }
                }
            });
        }
    }
});
