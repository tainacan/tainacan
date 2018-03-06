describe('Tainacan Test', function () {
  it('.should() - assert that <title> is correct', function () {
    cy.visit('http://localhost/wordpress/wp-admin')
    cy.get('log').type('admin').should('have.value', 'amdin')
    cy.get('pwd').type('root').should('have.value', 'root')
  })
})
