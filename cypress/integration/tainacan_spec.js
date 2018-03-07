describe('Plugin active Test', function () {
  beforeEach(() => {
    cy.loginByForm('admin', 'admin')
  })

  it('plugin active', function () {
    cy.get('h1').should('contain', 'Dashboard')
    cy.get('div').should('contain', 'Tainacan')
    cy.contains('Tainacan').click()
    cy.title().contains('Collections Page')
    cy.get('h1').should('contain', 'Collections Page')
  })
})
