describe('Tainacan Plugin Test', function () {

  beforeEach(() => {
    cy.loginByUI()
  })

  context('Collections', function(){
    it('canceled collection', function(){
      cy.visit('/wp-admin/admin.php?page=tainacan_admin#/collections')
      cy.get('h1').should('contain', 'Collections Page')
      cy.contains('New Collection').click()
      cy.get('[id=tainacan-text-name]').type('Book cancelado')
      cy.get('[id=button-cancel-collection-creation]').click()
      cy.url().should('contain', '/wp-admin/admin.php?page=tainacan_admin#/collections/')
      cy.get('h1').should('contain', 'Collections Page')
      cy.get('td').should('not.contain', 'Book cancelado')
    })

    it('status field blank collection', function(){
      cy.visit('/wp-admin/admin.php?page=tainacan_admin#/collections')
      cy.get('h1').should('contain', 'Collections Page')
      cy.contains('New Collection').click()
      cy.get('[id=tainacan-text-name]').type('Ebook status em branco')
      cy.get('[id=tainacan-text-description]').type('Importante a organização dos livros para estimular a leitura pelos usuários da biblioteca')
      cy.get('[id=button-submit-collection-creation]').click()
      cy.get('td').should('contain', 'New Item')
      cy.visit('/wp-admin/admin.php?page=tainacan_admin#/collections')
      cy.get('h1').should('contain', 'Collections Page')
      cy.get('td').should('not.contain', 'Book cancelado')

    })

    it('create collection', function(){
      cy.visit('/wp-admin/admin.php?page=tainacan_admin#/collections')
      cy.get('h1').should('contain', 'Collections Page')
      cy.contains('New Collection').click()
      cy.get('[id=tainacan-text-name]').type('Ebook 1')
      cy.get('[id=tainacan-text-description]').type('Importante a organização dos livros para estimular a leitura pelos usuários da biblioteca')
      cy.get('[id=tainacan-select-status]').select('Publish').should('have.value', 'publish')
      cy.get('[id=button-submit-collection-creation]').click()
      cy.get('td').should('contain', 'New Item')
      cy.visit('/wp-admin/admin.php?page=tainacan_admin#/collections')
      cy.get('td').should('contain', 'Ebook 1')
    })
  })
})
