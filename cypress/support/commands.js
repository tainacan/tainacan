// ***********************************************
// This example commands.js shows you how to
// create various custom commands and overwrite
// existing commands.
//
// For more comprehensive examples of custom
// commands please read more here:
// https://on.cypress.io/custom-commands
// ***********************************************
//
//
// -- This is a parent command --
 Cypress.Commands.add("login", (log, pwd) => {
//   cy.request({
//     method: 'POST'
//     url: baseUrl
//     body: {
//       email: 'admin@admin.com'
//       password 'admin'
//     }
//   })
//   .then((resp) => {
//     window.localStorage.setItem('jwt', resp.body.user.token)
//   })
   cy.visit('/wp-login.php')
   cy.get('[id=user_login]').type('admin@admin.com')
   cy.get('[id=user_pass]').type('admin{enter}')
   cy.hash().should('/wp-admin')
 })
//
//
// -- This is a child command --
// Cypress.Commands.add("drag", { prevSubject: 'element'}, (subject, options) => { ... })
//
//
// -- This is a dual command --
// Cypress.Commands.add("dismiss", { prevSubject: 'optional'}, (subject, options) => { ... })
//
//
// -- This is will overwrite an existing command --
// Cypress.Commands.overwrite("visit", (originalFn, url, options) => { ... })
