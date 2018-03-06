// <script src='/_cypress/tests?p=cypress/support/index.js'></script>
// <script src='/_cypress/tests?p=cypress/integration/login_spec.js'></script>
//
// describe('/wp-login.php', () => {
//   beforeEach(() => {
//     cy.visit('/wp-login.php')
//   })
//
//   it('requires email', () => {
//     cy.get('loginform').contains('Log in').click()
//     cy.get('.error-messages').should('contain','email can\'t te blank')
//   })
//
//   it('requires password', () => {
//     cy.get('[id=user_loginl]').type('admin@admin.com{enter}')
//     cy.get('.error-messages').should('contain','password can\'t te blank')
//   })
//
//   it('requires valid username and password', () => {
//     cy.get('[id=user_login]').type('admin@admin.com')
//     cy.get('[id=user_pass]').type('invalid{enter}')
//     cy.get('.error-messages').should('contain', 'email or password is invalid')
//   })
//
//   it('naviges to #/ on successful login', () => {
//     cy.get('[id=user_login]').type('admin@admin.com')
//     cy.get('[id=user_pass]').type('admin{enter}')
//     cy.hash().should('/wp-admin')
//   })
// })
