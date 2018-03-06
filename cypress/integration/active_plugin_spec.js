describe('Plugin active Test', function () {
  beforeEach(() => {
    cy.visit('/wp-login.php')
    cy.get('[id=user_login]').type('admin')
    cy.get('[id=user_pass]').type('admin')
    cy.get('[id=wp-submit]').click()
  })

  it('plugin active', function () {
    cy.contains('Tainacan').click()
  })
})
