let squareCard;

async function initializeSquare() {
    const button = document.getElementById('square-pay-btn');
    const cardContainer = document.getElementById('square-card-container');
    const status = document.getElementById('square-status');

    if (!button || !cardContainer || !status) {
        return;
    }

    const config = window.squareConfig || {};
    if (!config.applicationId || !config.locationId) {
        status.textContent = 'Square is not configured. Add the Sandbox Application ID and Location ID to Apache, then restart XAMPP.';
        return;
    }

    if (!/^sandbox-sq0idb-[A-Za-z0-9_-]+$/.test(config.applicationId)) {
        status.textContent = 'Invalid Square Sandbox Application ID. It should start with sandbox-sq0idb-; do not use the access token or Location ID.';
        return;
    }

    if (!/^L[A-Za-z0-9]+$/.test(config.locationId)) {
        status.textContent = 'Invalid Square Sandbox Location ID. It should start with L.';
        return;
    }

    if (!window.Square) {
        status.textContent = 'The Square payment SDK could not be loaded. Check your internet connection and browser console.';
        return;
    }

    try {
        const payments = window.Square.payments(
            config.applicationId,
            config.locationId
        );
        squareCard = await payments.card();
        await squareCard.attach('#square-card-container');
        button.disabled = false;
        status.textContent = '';
    } catch (error) {
        console.error(error);
        status.textContent = 'Square payment form could not be loaded: ' + error.message;
        button.disabled = true;
    }

    button.addEventListener('click', async () => {
        if (!squareCard) {
            status.textContent = 'Square is not ready yet. Check the message above.';
            return;
        }

        button.disabled = true;
        try {
            const result = await squareCard.tokenize();
            if (result.status !== 'OK') {
                throw new Error(result.errors?.[0]?.message || 'Square could not tokenize the card.');
            }

            const response = await fetch('square-payment.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({source_id: result.token})
            });
            const payment = await response.json();

            if (!response.ok) {
                throw new Error(payment.error || 'Square payment failed.');
            }

            window.location.href = 'success.php?gateway=square&transaction_id=' + encodeURIComponent(payment.payment_id);
        } catch (error) {
            console.error(error);
            status.textContent = error.message;
            button.disabled = false;
        }
    });
}

document.addEventListener('DOMContentLoaded', initializeSquare);
