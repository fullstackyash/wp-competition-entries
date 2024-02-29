document.addEventListener("DOMContentLoaded", function () {
	function ajaxPagination(page) {
		var request = new XMLHttpRequest();
		var params = `action=wce_competition_list&_ajaxnonce=${ajaxload_params.nonce}&page=${page}`;
		request.open("POST", ajaxload_params.ajax_url, true);
		request.setRequestHeader(
			"Content-Type",
			"application/x-www-form-urlencoded; charset=UTF-8"
		);
		request.onload = function ajaxLoad() {
			if (request.status >= 200 && request.status < 400) {
				var serverResponse = JSON.parse(request.responseText);
				var Obj = document.querySelector(".competition_list_wrapper");
				Obj.innerHTML = serverResponse.data; // replace element with contents of serverResponse
			}
		};

		request.send(params);
	}

	// selecting by querySelector
	liveQuery(".wce-universal-pagination li.active", "click", function (e) {
		if (e.target.hasAttribute("p")) {
			e.preventDefault();
			var page = e.target.getAttribute("p");
			ajaxPagination(page);
			return false;
		}
	});

	// selecting by querySelector
	function liveQuery(selector, eventType, callback, context) {
		(context || document).addEventListener(
			eventType,
			function (event) {
				var nodeList = document.querySelectorAll(selector);
				// convert nodeList into matches array
				var matches = [];
				for (var i = 0; i < nodeList.length; ++i) {
					matches.push(nodeList[i]);
				}
				// if there are matches
				if (matches) {
					var element = event.target;
					var index = -1;
					// traverse up the DOM tree until element can't be found in matches array
					while (
						element &&
						(index = matches.indexOf(element) === -1)
					) {
						element = element.parentElement;
					}
					// when element matches the selector, apply the callback
					if (index > -1) {
						callback.call(element, event);
					}
				}
			},
			false
		);
	}

	/**
	 * Entry form submission.
	 *
	 */
	document
		.querySelector("button#entry-submit")
		?.addEventListener("click", function (e) {
			e.preventDefault();
			const firstName =
				document.querySelector("input#first-name").value ?? "";
			const lastName =
				document.querySelector("input#last-name").value ?? "";
			const email = document.querySelector("input#email").value ?? "";
			const phone = document.querySelector("input#phone").value ?? "";
			const description =
				document.querySelector("textarea#description").value ?? "";
			const competitionId =
				document.querySelector("#competition-id").value ?? "";

			if (!firstName) {
				document
					.querySelector("input#first-name")
					.classList.add("error");
				document.querySelector(
					"input#first-name"
				).nextElementSibling.textContent = "*First Name is required";
				document.querySelector("form#entry-form").scrollIntoView();
			} else {
				document.querySelector(
					"input#first-name"
				).nextElementSibling.textContent = "";
				document
					.querySelector("input#first-name")
					.classList.remove("error");
			}

			if (!lastName) {
				document
					.querySelector("input#last-name")
					.classList.add("error");
				document.querySelector(
					"input#last-name"
				).nextElementSibling.textContent = "*Last Name is required";
				document.querySelector("form#entry-form").scrollIntoView();
			} else {
				document
					.querySelector("input#last-name")
					.classList.remove("error");
				document.querySelector(
					"input#last-name"
				).nextElementSibling.textContent = "";
			}

			if (!email) {
				document.querySelector("input#email").classList.add("error");
				document.querySelector(
					"input#email"
				).nextElementSibling.textContent = "*Email Id is required";
				document.querySelector("form#entry-form").scrollIntoView();
			} else {
				if (!validateEmail(email)) {
					document
						.querySelector("input#email")
						.classList.add("error");
					document.querySelector(
						"input#email"
					).nextElementSibling.textContent =
						"*Inappropriate Email Id";
					document.querySelector("form#entry-form").scrollIntoView();
				} else {
					document
						.querySelector("input#email")
						.classList.remove("error");
					document.querySelector(
						"input#email"
					).nextElementSibling.textContent = "";
				}
			}

			if (!phone) {
				document.querySelector(
					"input#phone"
				).nextElementSibling.textContent = "*Phone is required";
				document.querySelector("input#phone").classList.add("error");
				document.querySelector("form#entry-form").scrollIntoView();
			} else {
				document.querySelector("input#phone").classList.remove("error");
				document.querySelector(
					"input#phone"
				).nextElementSibling.textContent = "";
			}

			const urlParams = new URLSearchParams(window.location.search);
			const request = new XMLHttpRequest();
			if (
				firstName &&
				lastName &&
				phone &&
				email &&
				validateEmail(email)
			) {
				const params = `_ajaxnonce=${ajaxload_params.nonce}&firstName=${firstName}&lastName=${lastName}&phone=${phone}&email=${email}&description=${description}&competitionId=${competitionId}`;
				request.open("POST", ajaxload_params.ajax_url, true);
				request.setRequestHeader(
					"Content-Type",
					"application/x-www-form-urlencoded; charset=UTF-8"
				);
				request.onload = function ajaxLoad() {
					if (request.status >= 200 && request.status < 400) {
						const serverResponse = JSON.parse(request.responseText);
						const Obj = document.querySelector(".success");
						Obj.style.display = "block";
						Obj.innerHTML = serverResponse.data; // replace element with contents of serverResponse
						document.querySelector(
							"form#entry-form"
						).style.display = "none";
					}
				};

				request.send(`action=wce_submit_entry_form&${params}`);
			}
		});

	const validateEmail = (email) => {
		return String(email)
			.toLowerCase()
			.match(
				/^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|.(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
			);
	};
});
