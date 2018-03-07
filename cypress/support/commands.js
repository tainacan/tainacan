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
// Cypress.Commands.add("login", (email, password) => { ... })
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

Cypress.Commands.add('loginByForm', (username, password) => {

  Cypress.log({
    name: 'loginByForm',
    message: username + ' | ' + password
  })

  cy.request({
    method: 'POST',
    url: '/login',
    form: true,
    body: {
      log: username,
      pwd: password
    }
  })
  // we should be redirected to /wp-admin
  cy.url().should('include', '/wp-admin')
  cy.get('h1').should('contain', 'Dashboard')
})
