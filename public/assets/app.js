// Mejora progresiva: los flujos y la confirmación funcionan también sin JavaScript.
// Se anuncia el resumen y sus enlaces llevan a cada campo; funciona también sin este script.
const errorSummary = document.querySelector('[data-error-summary]');
if (errorSummary) errorSummary.focus();
