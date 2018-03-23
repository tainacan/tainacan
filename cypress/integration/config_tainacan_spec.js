describe('Configuration Plugin Test', function () {

  beforeEach(() => {
    cy.loginByUI()
  })

  context ('Configuration', function(){
    it('plugins active', function () {
      cy.visit('wp-admin/plugins.php')
      cy.get('.subsubsub > .active > a').click()
      cy.get('.wp-list-table').should('contain', 'Tainacan').and('contain', 'WP Cypress DB Cleaner')
    })

    it('WPress configuration', function(){
      cy.contains('WPress').click()
      cy.get('#default-api-namespace').clear()
      cy.get('#default-api-namespace').type('tainacan/v2')
      cy.get('#default-db-prefix').clear()
      cy.get('#default-db-prefix').type('wptests_')
      cy.get('#submit').click()
    })
  })
})
