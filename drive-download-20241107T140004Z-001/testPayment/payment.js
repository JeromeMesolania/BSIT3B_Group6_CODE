// Render the PayPal button into the container
paypal.Buttons({
  // Set up the transaction details
  createOrder: function(data, actions) {
    return actions.order.create({
      purchase_units: [{
        amount: {
          value: '10.00'  // Example amount ($10.00)
        }
      }]
    });
  },

  // Capture the transaction on approval
  onApprove: function(data, actions) {
    return actions.order.capture().then(function(details) {
      alert('Transaction completed successfully by ' + details.payer.name.given_name);
      console.log('Transaction details:', details);  // For debugging
    });
  },

  // Handle errors during the transaction
  onError: function(err) {
    console.error('Transaction error:', err);
    alert('An error occurred during the transaction. Please try again.');
  }
}).render('#paypal-button-container');
