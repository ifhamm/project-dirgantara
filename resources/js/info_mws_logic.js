document.addEventListener("DOMContentLoaded", function () {
    function makeEditable(el) {
        el.addEventListener("click", function () {
            const stepId = this.dataset.id;
            const field = this.dataset.field;
            const currentText = this.innerText === "-" ? "" : this.innerText;

            const input = document.createElement("input");
            input.value = currentText;
            input.classList.add("border", "px-1", "w-full");

            this.replaceWith(input);
            input.focus();

            const save = () => {
                fetch(`/mws-step/${stepId}/update`, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector(
                            'meta[name="csrf-token"]',
                        ).content,
                    },
                    body: JSON.stringify({
                        field: field,
                        value: input.value,
                    }),
                })
                    .then((res) => res.json())
                    .then((data) => {
                        const span = document.createElement("span");
                        span.classList.add("editable", "cursor-pointer");
                        span.dataset.id = stepId;
                        span.dataset.field = field;
                        span.innerText = input.value || "-";

                        input.replaceWith(span);

                        // rebind
                        makeEditable(span);
                    });
            };

            input.addEventListener("blur", save);

            input.addEventListener("keypress", function (e) {
                if (e.key === "Enter") {
                    input.blur();
                }
            });
        });
    }

    document.querySelectorAll(".editable").forEach(makeEditable);
});
