  context('HTML form submission', function(){
    beforeEach(function(){
      cy.visit('/login')
    })

    it('displays errors on login', function(){
      cy.get('input[name=log]').type('admin')
      cy.get('input[name=pwd]').type('senhaerrada{enter}')

      // and still be on the same URL
      cy.url().should('include', '/wp-login.php')
    })

    it('redirects to /dashboard on success', function(){
      cy.get('input[name=log]').type('admin')
      cy.get('input[name=pwd]').type('admin{enter}')

      // we should be redirected to /wp-admin
      cy.url().should('include', '/wp-admin')
      cy.get('h1').should('contain', 'Dashboard')
    })
  })

  context('HTML form submission with cy.request', function(){
    it('can bypass the UI and yet still test log in', function(){
      // oftentimes once we have a proper e2e test around logging in
      // there is NO more reason to actually use our UI to log in users
      // doing so wastes is slow because our entire page has to load,
      // all associated resources have to load, we have to fill in the
      // form, wait for the form submission and redirection process
      //
      // with cy.request we can bypass this because it automatically gets
      // and sets cookies under the hood. This acts exactly as if the requests
      // came from the browser
      cy.request({
          method: 'POST',
          url: '/login', // baseUrl will be prepended to this url
          form: true, // indicates the body should be form urlencoded and sets Content-Type: application/x-www-form-urlencoded headers
          body: {
            log: 'admin',
            pwd: 'admin'
          }
        })

        // just to prove we have a session
        cy.getCookie('cypress-session-cookie').should('null')
    })
  })

  context('Reusable "login" custom command', function(){
    // typically we'd put this in cypress/support/commands.js
    // but because this custom command is specific to this example
    // we'll keep it here
    Cypress.Commands.add('loginByForm', (username, password) => {

      Cypress.log({
        name: 'loginByForm',
        message: username + ' | ' + password
      })

      return cy.request({
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

    it('test loginByForm', function() {
      cy.url().should('include', '/wp-admin')
      cy.get('h1').should('contain', 'Dashboard')
      // login before each test
      cy.loginByForm('admin', 'admin')
    })
  })
