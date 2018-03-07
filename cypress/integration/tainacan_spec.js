describe('Plugin active Test', function () {
  beforeEach(() => {
    cy.loginByForm('admin', 'admin')
  })

  it('Visit users page', function () {
    cy.visit('/wp-admin/users.php')
    cy.get('h1').should('contain', 'Users')
  })

  it('plugin active', function () {
    cy.contains('Tainacan').click()
    cy.get('h1').should('contain', 'Collections Page')
  })
})
