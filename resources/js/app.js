import './bootstrap';
import QRCode from 'qrcode';

const menuToggle = document.querySelector('.menu-toggle');
const mainNavigation = document.querySelector('#main-navigation');
if (menuToggle && mainNavigation) {
	const header = menuToggle.closest('.topbar');
	const mobileNavigation = window.matchMedia('(max-width: 821px)');
	const closeNavigation = (restoreFocus = false) => {
		if (menuToggle.getAttribute('aria-expanded') !== 'true') {
			return;
		}

		menuToggle.setAttribute('aria-expanded', 'false');
		menuToggle.setAttribute('aria-label', 'Buka menu');
		mainNavigation.classList.remove('is-open');
		if (restoreFocus) {
			menuToggle.focus();
		}
	};

	document.addEventListener('keydown', (event) => {
		if (event.key === 'Escape') {
			closeNavigation(true);
		}
	});

	document.addEventListener('click', (event) => {
		if (header && !header.contains(event.target)) {
			closeNavigation();
		}
	});

	mainNavigation.addEventListener('click', (event) => {
		if (event.target.closest('a')) {
			closeNavigation();
		}
	});

	mobileNavigation.addEventListener('change', (event) => {
		if (!event.matches) {
			closeNavigation();
		}
	});
}

const adminHandoffNotice = document.querySelector('#admin-handoff-notice');
if (adminHandoffNotice) {
	const closeNotice = () => {
		adminHandoffNotice.hidden = true;
	};

	adminHandoffNotice.querySelector('.admin-handoff-close')?.addEventListener('click', closeNotice);
	adminHandoffNotice.addEventListener('click', (event) => {
		if (event.target === adminHandoffNotice) {
			closeNotice();
		}
	});
	document.addEventListener('keydown', (event) => {
		if (event.key === 'Escape') {
			closeNotice();
		}
	});
	window.setTimeout(closeNotice, 4000);
}

document.querySelectorAll('.loan-confirmation-qr').forEach((canvas) => {
	QRCode.toCanvas(canvas, canvas.dataset.token, {
		width: 320,
		margin: 4,
		errorCorrectionLevel: 'Q',
	});
});

document.addEventListener('click', (event) => {
	document.querySelectorAll('.loan-confirm[open]').forEach((loanConfirmation) => {
		if (!loanConfirmation.contains(event.target)) {
			loanConfirmation.removeAttribute('open');
		}
	});
});

document.querySelectorAll('.copy-qr-token').forEach((button) => {
	button.addEventListener('click', async () => {
		await navigator.clipboard.writeText(button.dataset.token);
		button.dataset.copied = 'true';
		button.textContent = 'Tersalin';
	});
});

const openBookDialog = (card) => {
	const dialog = document.getElementById(card.dataset.detailTrigger);
	if (dialog && !dialog.open) {
		dialog.showModal();
	}
};

document.querySelectorAll('.book-card[data-detail-trigger]').forEach((card) => {
	card.addEventListener('click', (event) => {
		if (event.target.closest('.book-detail-trigger, .book-actions, a, form, details, dialog')) {
			return;
		}

		openBookDialog(card);
	});
	card.addEventListener('keydown', (event) => {
		if (event.target === card && (event.key === 'Enter' || event.key === ' ')) {
			event.preventDefault();
			openBookDialog(card);
		}
	});
});

document.querySelectorAll('.book-detail-trigger').forEach((button) => {
	button.addEventListener('click', () => {
		const dialog = document.getElementById(button.getAttribute('aria-controls'));
		dialog?.showModal();
	});
});

document.querySelectorAll('.book-detail-dialog').forEach((dialog) => {
	dialog.querySelector('.book-detail-close')?.addEventListener('click', () => dialog.close());
	dialog.addEventListener('click', (event) => {
		if (event.target === dialog) {
			dialog.close();
		}
	});
});

document.querySelectorAll('.book-cover img, .book-detail-cover img').forEach((image) => {
	image.addEventListener('error', () => {
		image.hidden = true;
		const fallback = image.parentElement.querySelector('.cover-fallback');
		if (fallback) {
			fallback.hidden = false;
		}
	}, { once: true });
});

const pendingLoanRows = [...document.querySelectorAll('.loan-row[data-status-url]')];
const successNotice = document.querySelector('#loan-success-notice');
if (pendingLoanRows.length && successNotice) {
	const closeNotice = () => {
		successNotice.hidden = true;
	};
	let noticeTimer;

	successNotice.querySelector('.loan-success-close').addEventListener('click', closeNotice);
	successNotice.addEventListener('click', (event) => {
		if (event.target === successNotice) {
			closeNotice();
		}
	});
	document.addEventListener('keydown', (event) => {
		if (event.key === 'Escape' && !successNotice.hidden) {
			closeNotice();
		}
	});

	const checkLoanHandoffs = async () => {
		if (document.hidden || !pendingLoanRows.some((row) => row.dataset.loanStatus === 'pending')) {
			return;
		}

		try {
			const response = await fetch(pendingLoanRows[0].dataset.statusUrl, {
				headers: { 'Accept': 'application/json' },
				credentials: 'same-origin',
			});
			if (!response.ok) {
				return;
			}

			const { loans } = await response.json();
			for (const row of pendingLoanRows) {
				if (row.dataset.loanStatus !== 'pending') {
					continue;
				}

				const updatedLoan = loans.find((loan) => loan.id === Number(row.dataset.loanId));
				if (!updatedLoan || updatedLoan.status === 'pending') {
					continue;
				}

				row.dataset.loanStatus = updatedLoan.status;
				row.querySelector('.handoff-qr')?.remove();
				row.querySelector('.loan-handoff-state strong').textContent = updatedLoan.status === 'borrowed'
					? 'Buku sudah diserahkan'
					: `Status: ${updatedLoan.status}`;
				row.querySelector('.loan-due-state small').textContent = updatedLoan.status === 'borrowed' ? 'Jatuh tempo' : 'Status';
				row.querySelector('.loan-due-state strong').textContent = updatedLoan.due_date ?? '-';

				const statusBadge = row.querySelector('.loan-member-status');
				statusBadge.className = `loan-status status-${updatedLoan.status} loan-member-status`;
				statusBadge.textContent = updatedLoan.status.charAt(0).toUpperCase() + updatedLoan.status.slice(1);

				if (updatedLoan.status === 'borrowed') {
					successNotice.querySelector('.loan-success-book').textContent = row.querySelector('.loan-book > strong').textContent;
					successNotice.querySelector('.loan-success-due').textContent = `Jatuh tempo ${updatedLoan.due_date} · Selamat membaca`;
					const shelfMessage = updatedLoan.shelf_location
						? `Lokasi pengambilan: Rak ${updatedLoan.shelf_location}`
						: 'Lokasi rak belum diatur. Silakan tanyakan kepada petugas.';
					const shelfLocation = document.createElement('small');
					shelfLocation.className = 'loan-shelf-location';
					shelfLocation.textContent = shelfMessage;
					row.querySelector('.loan-book').append(shelfLocation);
					successNotice.querySelector('.loan-success-shelf').textContent = shelfMessage;
					successNotice.hidden = false;
					window.clearTimeout(noticeTimer);
					noticeTimer = window.setTimeout(closeNotice, 2000);
				}
			}
		} catch {
		}
	};

	window.setInterval(checkLoanHandoffs, 3000);
}

const scannerElement = document.querySelector('#handoff-reader');
if (scannerElement) {
	const { Html5Qrcode } = await import('html5-qrcode');
	const resultElement = document.querySelector('#scan-result');
	const manualForm = document.querySelector('#scan-manual');
	const manualTokenInput = document.querySelector('#scan-token');
	let scanner;
	let isSubmitting = false;
	const confirmedTokens = new Set();

	const submitToken = async (token) => {
		const normalizedToken = token.trim();
		if (confirmedTokens.has(normalizedToken)) {
			return;
		}

		if (!/^\d{5}$/.test(normalizedToken) || isSubmitting) {
			resultElement.dataset.state = 'error';
			resultElement.textContent = 'Kode tidak valid. Masukkan kode 5 digit atau pindai QR peminjaman.';
			return;
		}

		isSubmitting = true;
		resultElement.dataset.state = 'loading';
		resultElement.textContent = 'Memverifikasi peminjaman...';

		try {
			const response = await fetch(scannerElement.dataset.confirmUrl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'Accept': 'application/json',
					'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
				},
				body: JSON.stringify({ token: normalizedToken }),
			});
			let payload;
			try {
				payload = await response.json();
			} catch {
				throw new Error(`Server mengirim respons yang tidak valid (HTTP ${response.status}). Muat ulang halaman dan coba lagi.`);
			}

			if (!response.ok) {
				const validationMessage = payload?.errors?.token?.[0];
				if (validationMessage) {
					throw new Error(validationMessage);
				}

				throw new Error(`Server gagal mengonfirmasi QR (HTTP ${response.status}). Muat ulang halaman dan coba lagi.`);
			}

			confirmedTokens.add(normalizedToken);
			resultElement.dataset.state = 'success';
			resultElement.textContent = 'Serah-terima berhasil. Membuka halaman kelola status...';
			window.location.assign(scannerElement.dataset.successUrl);
		} catch (error) {
			resultElement.dataset.state = 'error';
			resultElement.textContent = error.message;
		} finally {
			isSubmitting = false;
		}
	};

	manualForm.addEventListener('submit', (event) => {
		event.preventDefault();
		void submitToken(manualTokenInput.value);
	});

	scanner = new Html5Qrcode('handoff-reader', { verbose: false });
	let isStarting = false;
	let shouldRunScanner = true;
	let pendingStop = Promise.resolve();

	const startScanner = async () => {
		if (isStarting) {
			return;
		}

		isStarting = true;
		try {
			await pendingStop;
			if (!shouldRunScanner || scanner.isScanning) {
				return;
			}

			await scanner.start(
				{ facingMode: 'environment' },
				{
					fps: 10,
					qrbox: { width: 240, height: 240 },
					aspectRatio: 1,
				},
				(decodedText) => submitToken(decodedText),
				() => {},
			);

			if (!shouldRunScanner) {
				pendingStop = scanner.stop().catch(() => {});
				await pendingStop;
				return;
			}

			resultElement.dataset.state = 'ready';
			resultElement.textContent = 'Kamera aktif. Arahkan QR peminjaman ke kamera.';
		} catch {
			if (shouldRunScanner) {
				resultElement.dataset.state = 'error';
				resultElement.textContent = 'Kamera tidak dapat dibuka. Izinkan akses kamera di browser lalu muat ulang halaman.';
			}
		} finally {
			isStarting = false;
		}
	};

	const stopScanner = () => {
		shouldRunScanner = false;
		if (scanner.isScanning) {
			pendingStop = scanner.stop().catch(() => {});
		}
		return pendingStop;
	};

	window.addEventListener('pagehide', stopScanner);
	window.addEventListener('pageshow', (event) => {
		if (event.persisted) {
			shouldRunScanner = true;
			void startScanner();
		}
	});

	void startScanner();
}
