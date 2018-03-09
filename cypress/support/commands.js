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
    name: 'loginByRequest',
    message: username + ' | ' + password
  })

  cy.request('/wp-admin')
  cy.get('title').should('contain', 'Log In ‹ Test — WordPress')

  cy.request({
    method: 'POST',
    url: '/wp-login.php',
    form: true,
    body: {
      log: username,
      pwd: password
    }
  })

  cy.get('h1').should('contain', 'Dashboard')

  cy.getCookie('cypress-session-cookie').should('exist')
})

Cypress.Commands.add('loginByRequest', () => {

  Cypress.log({
    name: 'loginByRequest',
    message: 'admin' + ' | ' + 'admin'
  })

  cy.request({
    method: 'POST',
    url: '/login',
    form: true,
    body: {
      log: 'admin',
      pwd: 'admin'
    }
  })

  // we should be redirected to /wp-admin
  cy.url().should('include', '/wp-admin')
  cy.get('h1').should('contain', 'Dashboard')

  cy.getCookie('cypress-session-cookie').should('exist')
})

Cypress.Commands.add('loginByUI', () => {
    cy.visit('/wp-admin')
    cy.get('input[name=log]').type('admin')
    cy.get('input[name=pwd]').type('admin{enter}')
    // we should be redirected to /wp-admin
    cy.url().should('include', '/wp-admin')
    cy.get('h1').should('contain', 'Dashboard')
  })
